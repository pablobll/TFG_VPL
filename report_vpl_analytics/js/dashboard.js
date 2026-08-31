(function(window, document) {
const lang = window.VplAnalyticsLang;
document.addEventListener('DOMContentLoaded', function() {

    const rawData = window.VplAnalyticsData;
    
    function getSelectedScale() {
        return document.getElementById('settingGradeScale').value || '10';
    }

    function formatGradeStr(normalizedGrade) {
        if (normalizedGrade === null || isNaN(normalizedGrade)) return '--';
        let scale = getSelectedScale();
        if (scale === '100') {
            return (normalizedGrade * 100).toFixed(1);
        } else if (scale === 'letters') {
            if (normalizedGrade >= 0.9) return 'A';
            if (normalizedGrade >= 0.8) return 'B';
            if (normalizedGrade >= 0.7) return 'C';
            if (normalizedGrade >= 0.6) return 'D';
            return 'F';
        } else {
            return (normalizedGrade * 10).toFixed(2);
        }
    }

    function getGradeColor(normalizedGrade) {
        if (normalizedGrade === null || isNaN(normalizedGrade)) return 'transparent';
        if (normalizedGrade >= 0.7) return '#28a745';
        if (normalizedGrade >= 0.5) return '#ffc107';
        return '#dc3545';
    }

    function getNormalizedStagnantGrade() {
        let stagVal = parseFloat(document.getElementById('settingStagnantGrade').value) || 5.0;
        let scale = getSelectedScale();
        if (scale === '100') return stagVal / 100.0;
        return stagVal / 10.0;
    }

    let currentChart = null;
    const primaryColor = '#007bff';
    const secondaryColor = '#9bca3e';
    
    const vplDict = {};
    if (rawData.vpls) {
        rawData.vpls.forEach(v => vplDict[v.id] = v);
    }

    let expandedSubmissions = [];
    if (rawData.submissions && rawData.users && rawData.user_groups_map) {
        rawData.submissions.forEach(s => {
            if (s.groupid && s.groupid > 0) {
                let groupMembers = rawData.users.filter(uid => rawData.user_groups_map[uid] && rawData.user_groups_map[uid].includes(s.groupid));
                if (groupMembers.length > 0) {
                    groupMembers.forEach(uid => {
                        expandedSubmissions.push({ ...s, id: s.id + '_' + uid, userid: uid });
                    });
                } else {
                    expandedSubmissions.push(s);
                }
            } else {
                expandedSubmissions.push(s);
            }
        });
        rawData.submissions = expandedSubmissions;
    }

    const analysisModeEl = document.getElementById('analysisMode');
    const chartTypeEl = document.getElementById('chartType');
    const filterGroupEl = document.getElementById('filterGroup');
    const filterVplEl = document.getElementById('filterVpl');
    
    const panelGlobal = document.getElementById('panelGlobal');
    const panelCompareGroups = document.getElementById('panelCompareGroups');
    const panelCompareUsers = document.getElementById('panelCompareUsers');

    const compareGroup1El = document.getElementById('compareGroup1');
    const compareGroup2El = document.getElementById('compareGroup2');
    const compareUser1El = document.getElementById('compareUser1');
    const compareUser2El = document.getElementById('compareUser2');
    
    const zoomOptions = {
        pan: { enabled: true, mode: 'xy' },
        zoom: { wheel: { enabled: false }, pinch: { enabled: false }, mode: 'xy' }
    };

    document.getElementById('btnZoomIn').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(1.2);
    });
    document.getElementById('btnZoomOut').addEventListener('click', () => {
        if (currentChart && typeof currentChart.zoom === 'function') currentChart.zoom(0.8);
    });
    document.getElementById('btnZoomReset').addEventListener('click', () => {
        if (currentChart && typeof currentChart.resetZoom === 'function') currentChart.resetZoom();
    });
    
    document.getElementById('btnSettings').addEventListener('click', () => document.getElementById('settingsModal').style.display = 'flex');
    document.getElementById('btnSettingsCancel').addEventListener('click', () => document.getElementById('settingsModal').style.display = 'none');
    document.getElementById('btnSettingsSave').addEventListener('click', () => {
        document.getElementById('settingsModal').style.display = 'none';
        updateDashboard();
    });

    let groupMap = {};
    let groupCountMap = {};
    rawData.groups.forEach(g => {
        groupMap[g.id] = g.name;
        groupCountMap[g.id] = g.member_count;
        
        let opt1 = document.createElement('option'); opt1.value = g.id; opt1.innerText = g.name;
        filterGroupEl.appendChild(opt1);
        
        let opt2 = document.createElement('option'); opt2.value = g.id; opt2.innerText = g.name;
        compareGroup1El.appendChild(opt2);
        
        let opt3 = document.createElement('option'); opt3.value = g.id; opt3.innerText = g.name;
        compareGroup2El.appendChild(opt3);
    });
    
    rawData.users.forEach(uid => {
        let uName = rawData.user_names_map && rawData.user_names_map[uid] ? rawData.user_names_map[uid] : uid;
        let opt1 = document.createElement('option');
        opt1.value = uid; opt1.innerText = uName + ' (ID: ' + uid + ')';
        compareUser1El.appendChild(opt1);
        
        let opt2 = document.createElement('option');
        opt2.value = uid; opt2.innerText = uName + ' (ID: ' + uid + ')';
        compareUser2El.appendChild(opt2);
    });

    if (rawData.vpls) {
        const categories = [
            { id: 'cat_graded', text: lang.cat_graded },
            { id: 'cat_ungraded', text: lang.cat_ungraded },
            { id: 'cat_open', text: lang.cat_open },
            { id: 'cat_closed', text: lang.cat_closed },
            { id: 'cat_group', text: lang.cat_group },
            { id: 'cat_individual', text: lang.cat_individual }
        ];
        
        categories.forEach(c => {
            let opt = document.createElement('option');
            opt.value = c.id; opt.innerText = c.text;
            filterVplEl.appendChild(opt);
        });

        let sep = document.createElement('option');
        sep.disabled = true; sep.innerText = '──────────';
        filterVplEl.appendChild(sep);

        let sections = {};
        rawData.vpls.forEach(v => {
            if (!sections[v.section]) sections[v.section] = [];
            sections[v.section].push(v);
        });
        
        Object.keys(sections).forEach(secName => {
            let secOpt = document.createElement('option');
            secOpt.value = 'sec_' + secName;
            secOpt.innerText = secName;
            secOpt.style.fontWeight = 'bold';
            filterVplEl.appendChild(secOpt);
            
            sections[secName].forEach(v => {
                let opt = document.createElement('option');
                opt.value = v.id; 
                opt.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;' + v.name;
                filterVplEl.appendChild(opt);
            });
        });
    } else {
        let uniqueVpls = {};
        rawData.submissions.forEach(s => { uniqueVpls[s.vpl] = s.vpl_name; });
        Object.keys(uniqueVpls).forEach(vplId => {
            let opt = document.createElement('option');
            opt.value = vplId; opt.innerText = uniqueVpls[vplId];
            filterVplEl.appendChild(opt);
        });
    }

    analysisModeEl.addEventListener('change', () => {
        panelGlobal.style.display = 'none';
        panelCompareGroups.style.display = 'none';
        panelCompareUsers.style.display = 'none';
        
        if (analysisModeEl.value === 'global' || analysisModeEl.value === 'matriz') {
            panelGlobal.style.display = 'flex';
        } else {
            filterVplEl.value = 'all';
            filterGroupEl.value = 'all';
            if (analysisModeEl.value === 'compare_groups') panelCompareGroups.style.display = 'flex';
            else if (analysisModeEl.value === 'compare_users') panelCompareUsers.style.display = 'flex';
        }
        
        updateDashboard();
    });

    const filterDateFromEl = document.getElementById('filterDateFrom');
    const filterDateToEl = document.getElementById('filterDateTo');
    
    [chartTypeEl, filterGroupEl, filterVplEl, compareGroup1El, compareGroup2El, compareUser1El, compareUser2El, filterDateFromEl, filterDateToEl].forEach(el => el.addEventListener('change', updateDashboard));

    function updateDashboard() {
        const heatOption = Array.from(chartTypeEl.options).find(opt => opt.value === 'heatmap_tiempo');
        if (heatOption) { heatOption.disabled = false; heatOption.style.display = ''; }
        
        let isSpecificVpl = !isNaN(parseInt(filterVplEl.value)) && !filterVplEl.value.startsWith('cat_') && !filterVplEl.value.startsWith('sec_') && filterVplEl.value !== 'all';
        
        if (analysisModeEl.value !== 'global') {
            if (heatOption) { heatOption.disabled = true; heatOption.style.display = 'none'; }
            if (chartTypeEl.value === 'heatmap_tiempo') {
                chartTypeEl.value = 'rendimiento';
            }
        }

        const mode = analysisModeEl.value;
        const type = chartTypeEl.value;
        const vplId = filterVplEl.value;

        let baseFiltered = rawData.submissions;
        
        let tsFrom = filterDateFromEl.value ? new Date(filterDateFromEl.value).getTime() / 1000 : 0;
        let tsTo = filterDateToEl.value ? new Date(filterDateToEl.value).getTime() / 1000 + 86399 : Infinity;
        if (tsFrom > 0 || tsTo < Infinity) {
            baseFiltered = baseFiltered.filter(s => s.datesubmitted >= tsFrom && s.datesubmitted <= tsTo);
        }

        if (vplId !== 'all') {
            if (vplId === 'cat_graded') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && vplDict[s.vpl].graded);
            } else if (vplId === 'cat_ungraded') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && !vplDict[s.vpl].graded);
            } else if (vplId === 'cat_open') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && !vplDict[s.vpl].closed);
            } else if (vplId === 'cat_closed') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && vplDict[s.vpl].closed);
            } else if (vplId === 'cat_group') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && vplDict[s.vpl].is_group);
            } else if (vplId === 'cat_individual') {
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && !vplDict[s.vpl].is_group);
            } else if (vplId.startsWith('sec_')) {
                const targetSec = vplId.substring(4);
                baseFiltered = baseFiltered.filter(s => vplDict[s.vpl] && vplDict[s.vpl].section === targetSec);
            } else {
                baseFiltered = baseFiltered.filter(s => s.vpl == vplId);
            }
        }

        let datasetsInfo = [];
        
        if (mode === 'matriz') {
            document.querySelector('.vpl-canvas-container').style.display = 'none';
            document.getElementById('tableTopControls').style.display = 'flex';
            document.getElementById('mainTableContainer').style.display = 'block';
            document.querySelector('.vpl-kpi-container').style.display = 'none';
            document.getElementById('btnExportCSV').style.display = 'none';
            if (chartTypeEl.parentElement) chartTypeEl.parentElement.style.display = 'none';
            
            const groupId = filterGroupEl.value;
            let finalData = baseFiltered;
            if (groupId !== 'all') {
                const gid = parseInt(groupId);
                finalData = finalData.filter(s => s.user_groups && s.user_groups.includes(gid));
                updateTable(finalData, [gid], null);
            } else {
                updateTable(finalData, null, null);
            }
            return;
        } else {
            document.querySelector('.vpl-canvas-container').style.display = 'block';
            document.querySelector('.vpl-kpi-container').style.display = 'flex';
            document.getElementById('btnExportCSV').style.display = '';
            if (chartTypeEl.parentElement) chartTypeEl.parentElement.style.display = 'block';
        }

        if (mode === 'global') {
            const groupId = filterGroupEl.value;
            let finalData = baseFiltered;
            let currentTotalStudents = rawData.total_students;
            
            if (groupId !== 'all') {
                const gid = groupId;
                finalData = finalData.filter(s => s.user_groups && s.user_groups.some(g => g == gid));
                currentTotalStudents = groupCountMap[gid] || 0;
            }
            datasetsInfo.push({ label: 'Global', data: finalData, color: primaryColor });
            updateKPIs(finalData, currentTotalStudents);
            
            if (groupId === 'all') {
                document.getElementById('tableTopControls').style.display = 'none';
                document.getElementById('mainTableContainer').style.display = 'none';
            } else {
                document.getElementById('tableTopControls').style.display = 'flex';
                document.getElementById('mainTableContainer').style.display = 'block';
                updateTable(finalData, [parseInt(groupId)], null);
            }
            
        } else if (mode === 'compare_groups') {
            const gid1 = compareGroup1El.value;
            const gid2 = compareGroup2El.value;
            if (gid1 === 'none' && gid2 === 'none') {
                document.getElementById('mainTableContainer').style.display = 'none';
                document.getElementById('tableTopControls').style.display = 'none';
                document.querySelector('.vpl-canvas-container').style.display = 'none';
                document.querySelector('.vpl-kpi-container').style.display = 'none';
                return;
            }
            
            let combined = [];
            let allowedGroups = [];
            
            if (gid1 !== 'none') {
                let d1 = baseFiltered.filter(s => s.user_groups && s.user_groups.some(g => g == gid1));
                datasetsInfo.push({ label: groupMap[gid1] || lang.label_group + ' ' + gid1, data: d1, color: primaryColor });
                combined.push(...d1);
                allowedGroups.push(gid1);
            }
            if (gid2 !== 'none') {
                let d2 = baseFiltered.filter(s => s.user_groups && s.user_groups.some(g => g == gid2));
                datasetsInfo.push({ label: groupMap[gid2] || lang.label_group + ' ' + gid2, data: d2, color: secondaryColor });
                combined.push(...d2);
                allowedGroups.push(gid2);
            }
            
            let combinedMap = new Map();
            combined.forEach(s => combinedMap.set(s.id, s));
            combined = Array.from(combinedMap.values());
            
            let allowedUsers = new Set();
            if (rawData.users && rawData.user_groups_map) {
                rawData.users.forEach(uid => {
                    let uGroups = rawData.user_groups_map[uid] || [0];
                    if (allowedGroups.some(gid => uGroups.some(g => g == gid))) {
                        allowedUsers.add(uid);
                    }
                });
            }
            let combinedTotal = allowedUsers.size > 0 ? allowedUsers.size : allowedGroups.reduce((acc, gid) => acc + (groupCountMap[gid] || 0), 0);
            
            updateKPIs(combined, combinedTotal);
            
            document.getElementById('mainTableContainer').style.display = 'block';
            document.getElementById('tableTopControls').style.display = 'flex';
            updateTable(combined, allowedGroups, null);
        } else if (mode === 'compare_users') {
            const uid1 = compareUser1El.value;
            const uid2 = compareUser2El.value;
            if (uid1 === 'none' && uid2 === 'none') {
                document.getElementById('mainTableContainer').style.display = 'none';
                document.getElementById('tableTopControls').style.display = 'none';
                document.querySelector('.vpl-canvas-container').style.display = 'none';
                document.querySelector('.vpl-kpi-container').style.display = 'none';
                return;
            }
            let combined = [];
            let allowedUsersList = [];
            
            if (uid1 !== 'none') {
                let uName1 = rawData.user_names_map && rawData.user_names_map[uid1] ? rawData.user_names_map[uid1] : uid1;
                let d1 = baseFiltered.filter(s => s.userid == uid1);
                datasetsInfo.push({ label: uName1, data: d1, color: primaryColor });
                combined.push(...d1);
                allowedUsersList.push(uid1);
            }
            if (uid2 !== 'none') {
                let uName2 = rawData.user_names_map && rawData.user_names_map[uid2] ? rawData.user_names_map[uid2] : uid2;
                let d2 = baseFiltered.filter(s => s.userid == uid2);
                datasetsInfo.push({ label: uName2, data: d2, color: secondaryColor });
                combined.push(...d2);
                allowedUsersList.push(uid2);
            }
            
            let combinedMap = new Map();
            combined.forEach(s => combinedMap.set(s.id, s));
            combined = Array.from(combinedMap.values());
            
            let combinedTotal = allowedUsersList.length > 0 ? new Set(allowedUsersList).size : 0;
            updateKPIs(combined, combinedTotal);
            
            document.getElementById('mainTableContainer').style.display = 'block';
            document.getElementById('tableTopControls').style.display = 'flex';
            updateTable(combined, null, allowedUsersList);
        }

        renderChart(type, datasetsInfo);
    }

    function updateKPIs(subs, totalStudentsFallback) {
        let totalAllowed = totalStudentsFallback !== undefined ? totalStudentsFallback : (rawData.total_students || 0);
        
        if (subs.length === 0) {
            document.getElementById('kpiAvgGrade').innerText = '0.00';
            document.getElementById('kpiTotalSubs').innerText = '0';
            document.getElementById('kpiActiveUsers').innerText = '0';
            document.getElementById('kpiInactiveUsers').innerText = totalAllowed;
            return;
        }

        let activeUsers = new Set();
        let finalGrades = {};
        
        subs.forEach(s => {
            activeUsers.add(s.userid);
            if (s.grade !== null) {
                let key = s.userid + '_' + s.vpl;
                if (finalGrades[key] === undefined || s.datesubmitted > finalGrades[key].date) {
                    finalGrades[key] = { grade: s.grade, date: s.datesubmitted };
                }
            }
        });

        let sumGrades = 0;
        let countGrades = 0;
        let passCount = 0;
        let excCount = 0;
        let gradeArray = [];
        Object.values(finalGrades).forEach(g => {
            sumGrades += g.grade;
            countGrades++;
            gradeArray.push(g.grade);
            if (g.grade >= 0.5) passCount++;
            if (g.grade >= 0.9) excCount++;
        });

        let avgStr = countGrades > 0 ? formatGradeStr(sumGrades / countGrades) : formatGradeStr(0);
        let passStr = countGrades > 0 ? ((passCount / countGrades) * 100).toFixed(1) + '%' : '0.0%';
        let excStr = countGrades > 0 ? ((excCount / countGrades) * 100).toFixed(1) + '%' : '0.0%';
        
        let medianStr = formatGradeStr(0);
        if (gradeArray.length > 0) {
            gradeArray.sort((a,b) => a - b);
            let mid = Math.floor(gradeArray.length / 2);
            let median = gradeArray.length % 2 !== 0 ? gradeArray[mid] : (gradeArray[mid - 1] + gradeArray[mid]) / 2.0;
            medianStr = formatGradeStr(median);
        }

        const elAvg = document.getElementById('kpiAvgGrade');
        if(elAvg) elAvg.innerText = avgStr;
        
        const elPass = document.getElementById('kpiPassRate');
        if(elPass) elPass.innerText = passStr;
        const elExc = document.getElementById('kpiExcRate');
        if(elExc) elExc.innerText = excStr;
        const elMed = document.getElementById('kpiMedian');
        if(elMed) elMed.innerText = medianStr;

        const totalSubs = subs.length;
        const activeCount = activeUsers.size;
        const inactiveCount = Math.max(0, totalAllowed - activeCount);

        const elTotalSubs = document.getElementById('kpiTotalSubs');
        if(elTotalSubs) elTotalSubs.innerText = totalSubs;
        
        const elActiveUsers = document.getElementById('kpiActiveUsers');
        if(elActiveUsers) elActiveUsers.innerText = activeCount + (totalAllowed ? ' / ' + totalAllowed : '');
        
        const elInactiveUsers = document.getElementById('kpiInactiveUsers');
        if(elInactiveUsers) elInactiveUsers.innerText = inactiveCount;
    }

    function updateTable(subs, allowedGroupIds = null, allowedUserIds = null) {
        const tbody = document.getElementById('dataTableBody');
        tbody.innerHTML = '';
        
        let isSpecificVpl = !isNaN(parseInt(filterVplEl.value)) && !filterVplEl.value.startsWith('cat_') && !filterVplEl.value.startsWith('sec_') && filterVplEl.value !== 'all';

        let studentStats = {};
        subs.forEach(s => {
            if (!studentStats[s.userid]) {
                let uGroups = rawData.user_groups_map[s.userid] || [];
                if (uGroups.length === 0) uGroups = [0];
                let gNames = uGroups.map(gid => groupMap[gid] || gid).join(', ');
                if (!gNames) gNames = lang.label_no_group;
                
                studentStats[s.userid] = {
                    group: gNames, subs: 0, finalGrade: null, lastGradeDate: 0,
                    firstSub: s.datesubmitted, lastSub: s.datesubmitted,
                    vplMaxEffort: {},
                    isProcrastinator: false
                };
            }
            let st = studentStats[s.userid];
            st.subs++;
            
            if (s.grade !== null) {
                if (st.finalGrade === null || s.datesubmitted > st.lastGradeDate) {
                    st.finalGrade = s.grade;
                    st.lastGradeDate = s.datesubmitted;
                }
            }
            if (s.datesubmitted < st.firstSub) st.firstSub = s.datesubmitted;
            if (s.datesubmitted > st.lastSub) st.lastSub = s.datesubmitted;
            
            if (!st.vplMaxEffort[s.vpl]) {
                st.vplMaxEffort[s.vpl] = { runs: 0, evals: 0, debugs: 0, firstSub: s.datesubmitted, finalGrade: null, lastSub: s.datesubmitted };
            }
            if (s.datesubmitted < st.vplMaxEffort[s.vpl].firstSub) st.vplMaxEffort[s.vpl].firstSub = s.datesubmitted;
            if (s.datesubmitted > st.vplMaxEffort[s.vpl].lastSub) st.vplMaxEffort[s.vpl].lastSub = s.datesubmitted;
            if (s.grade !== null) {
                if (st.vplMaxEffort[s.vpl].finalGrade === null || s.datesubmitted > st.vplMaxEffort[s.vpl].lastSub) {
                    st.vplMaxEffort[s.vpl].finalGrade = s.grade;
                    st.vplMaxEffort[s.vpl].lastSub = s.datesubmitted;
                }
            }
            if (s.run_count > st.vplMaxEffort[s.vpl].runs) st.vplMaxEffort[s.vpl].runs = s.run_count;
            if (s.nevaluations > st.vplMaxEffort[s.vpl].evals) st.vplMaxEffort[s.vpl].evals = s.nevaluations;
            if (s.debug_count && s.debug_count > st.vplMaxEffort[s.vpl].debugs) st.vplMaxEffort[s.vpl].debugs = s.debug_count;
        });

        if (rawData.users && rawData.user_groups_map) {
            rawData.users.forEach(uid => {
                if (allowedUserIds !== null) {
                    if (!allowedUserIds.some(id => id == uid)) return;
                } else {
                    let uGroups = rawData.user_groups_map[uid] || [];
                    if (uGroups.length === 0) uGroups = [0];
                    if (allowedGroupIds !== null) {
                        let hasMatch = allowedGroupIds.some(gid => uGroups.some(g => g == gid));
                        if (!hasMatch) return;
                    }
                }

                if (!studentStats[uid]) {
                    let uGroups = rawData.user_groups_map[uid] || [];
                    if (uGroups.length === 0) uGroups = [0];
                    let gNames = uGroups.map(gid => groupMap[gid] || gid).join(', ');
                    if (!gNames || gNames === '0') gNames = lang.label_no_group;

                    studentStats[uid] = {
                        group: gNames, subs: 0, finalGrade: null, lastGradeDate: 0,
                        firstSub: null, lastSub: null,
                        vplMaxEffort: {},
                        isProcrastinator: false
                    };
                }
            });
        }

        let sortedUsers = Object.keys(studentStats).sort((a,b) => {
            let nameA = rawData.user_names_map && rawData.user_names_map[a] ? rawData.user_names_map[a].toLowerCase() : a.toString();
            let nameB = rawData.user_names_map && rawData.user_names_map[b] ? rawData.user_names_map[b].toLowerCase() : b.toString();
            return nameA.localeCompare(nameB);
        });
        if (sortedUsers.length === 0) {
            let colCount = isSpecificVpl ? 9 : 8;
            tbody.innerHTML = '<tr><td colspan=\'' + colCount + '\' style=\'text-align:center\'>' + lang.label_no_students + '</td></tr>';
            return;
        }

        if (analysisModeEl.value === 'matriz') {
            let activeVpls = new Set();
            subs.forEach(s => activeVpls.add(s.vpl));
            let vplList = Array.from(activeVpls).map(id => vplDict[id]).filter(v => v).sort((a,b) => (a.course_order || 99999) - (b.course_order || 99999));
            
            let theadHtml = '<tr><th>' + lang.col_student + '</th><th>' + lang.col_group + '</th>';
            vplList.forEach(v => {
                let shortName = v.name.length > 15 ? v.name.substring(0,12) + '...' : v.name;
                theadHtml += '<th title=\'' + v.name + '\' style=\'text-align:center; min-width:80px;\'>' + shortName + '</th>';
            });
            theadHtml += '</tr>';
            document.querySelector('.vpl-table ' + 'thead').innerHTML = theadHtml;
            
            sortedUsers.forEach(uid => {
                let st = studentStats[uid];
                let uName = rawData.user_names_map && rawData.user_names_map[uid] ? rawData.user_names_map[uid] : uid;
                let uLink = '<a href=\"../../user/view.php?id=' + uid + '&course=' + window.VplAnalyticsCourseId + '\" target=\"_blank\" style=\"text-decoration:none; color:#007bff; font-weight:bold;\">' + uName + '</a>';
                let isStagnant = false;
                let isProcInit = false;
                let isProcFinal = false;
                let stagEvals = parseInt(document.getElementById('settingStagnantEvals').value) || 15;
                let stagGrade = getNormalizedStagnantGrade();
                let procInitHours = parseFloat(document.getElementById('settingProcInitHours').value) || 48;
                let procFinalHours = parseFloat(document.getElementById('settingProcFinalHours').value) || 2;
                
                Object.keys(st.vplMaxEffort).forEach(vplId => {
                    let v = st.vplMaxEffort[vplId];
                    if (v.evals >= stagEvals && (v.finalGrade === null || v.finalGrade < stagGrade)) {
                        isStagnant = true;
                    }
                    let vDue = vplDict[vplId] ? vplDict[vplId].duedate : 0;
                    if (vDue > 0) {
                        if (v.firstSub >= (vDue - (procInitHours * 3600))) isProcInit = true;
                        if (v.lastGradeDate >= (vDue - (procFinalHours * 3600))) isProcFinal = true;
                    }
                });
                
                let badges = '';
                if (isStagnant) badges += ' <span style=\'background:#dc3545; color:white; padding:2px 6px; border-radius:10px; font-size:0.75em;\' title=\'' + lang.badge_risk_desc + '\'>' + lang.badge_risk + '</span>';
                if (isProcInit) badges += ' <span style=\'background:#ffc107; color:black; padding:2px 6px; border-radius:10px; font-size:0.75em;\' title=\'' + lang.badge_proc_init_desc + '\'>' + lang.badge_proc_init + '</span>';
                if (isProcFinal) badges += ' <span style=\'background:#fd7e14; color:white; padding:2px 6px; border-radius:10px; font-size:0.75em;\' title=\'' + lang.badge_proc_final_desc + '\'>' + lang.badge_proc_final + '</span>';
                
                let tr = document.createElement('tr');
                let rowHtml = '<td>' + uLink + badges + '</td><td>' + st.group + '</td>';
                
                vplList.forEach(v => {
                    let vEffort = st.vplMaxEffort[v.id];
                    if (!vEffort || vEffort.finalGrade === null) {
                        rowHtml += '<td style=\'background:#f8f9fa; color:#adb5bd; text-align:center;\'>-</td>';
                    } else {
                        let grade = vEffort.finalGrade;
                        let bg = getGradeColor(grade);
                        let color = (grade >= 0.5 && grade < 0.7) ? 'black' : 'white';
                        rowHtml += '<td style=\'background:' + bg + '; color:' + color + '; font-weight:bold; text-align:center;\'>' + formatGradeStr(grade) + '</td>';
                    }
                });
                tr.innerHTML = rowHtml;
                tbody.appendChild(tr);
            });
            return;
        }

        document.querySelector('.vpl-table ' + 'thead').innerHTML = '<tr><th>' + lang.col_student + '</th><th>' + lang.col_group + '</th><th>' + lang.label_subs + '</th><th class=\'col-grade\'>' + lang.label_avg_grade + '</th><th>First Sub</th><th>Last Sub</th><th>' + lang.label_runs + '</th><th>Debugs</th><th>' + lang.label_evals + '</th></tr>';
        
        let gradeHeaderNode = document.querySelector('.vpl-table thead th.col-grade');
        if (gradeHeaderNode) gradeHeaderNode.style.display = isSpecificVpl ? '' : 'none';

        sortedUsers.forEach(uid => {
            let st = studentStats[uid];
            let dFirst = st.firstSub ? new Date(st.firstSub * 1000).toLocaleDateString() : '--';
            let dLast = st.lastSub ? new Date(st.lastSub * 1000).toLocaleDateString() : '--';
            
            let gradeStr = formatGradeStr(st.finalGrade);
            
            let totalRuns = 0;
            let totalEvals = 0;
            let totalDebugs = 0;
            let isStagnant = false;
            let isProcInit = false;
            let isProcFinal = false;
            let stagEvals = parseInt(document.getElementById('settingStagnantEvals').value) || 15;
            let stagGrade = getNormalizedStagnantGrade();
            let procInitHours = parseFloat(document.getElementById('settingProcInitHours').value) || 48;
            let procFinalHours = parseFloat(document.getElementById('settingProcFinalHours').value) || 2;
            
            if (st.vplMaxEffort) {
                Object.keys(st.vplMaxEffort).forEach(vplId => {
                    let v = st.vplMaxEffort[vplId];
                    totalRuns += v.runs || 0;
                    totalEvals += v.evals || 0;
                    totalDebugs += v.debugs || 0;
                    
                    if (v.evals >= stagEvals && (v.finalGrade === null || v.finalGrade < stagGrade)) {
                        isStagnant = true;
                    }
                    
                    let vDue = vplDict[vplId] ? vplDict[vplId].duedate : 0;
                    if (vDue > 0) {
                        if (v.firstSub >= (vDue - (procInitHours * 3600))) isProcInit = true;
                        if (v.lastSub >= (vDue - (procFinalHours * 3600))) isProcFinal = true;
                    }
                });
            }
            
            let uName = rawData.user_names_map && rawData.user_names_map[uid] ? rawData.user_names_map[uid] : uid;
            let uLink = '<a href=\"../../user/view.php?id=' + uid + '&course=' + window.VplAnalyticsCourseId + '\" target=\"_blank\" style=\"text-decoration:none; color:#007bff; font-weight:bold;\">' + uName + '</a>';
            
            let badges = '';
            if (isStagnant) badges += ' <span style="background:#dc3545; color:white; padding:2px 6px; border-radius:10px; font-size:0.75em;" title="' + lang.badge_risk_desc + '">' + lang.badge_risk + '</span>';
            if (isProcInit) badges += ' <span style="background:#ffc107; color:black; padding:2px 6px; border-radius:10px; font-size:0.75em;" title="' + lang.badge_proc_init_desc + '">' + lang.badge_proc_init + '</span>';
            if (isProcFinal) badges += ' <span style="background:#fd7e14; color:white; padding:2px 6px; border-radius:10px; font-size:0.75em;" title="' + lang.badge_proc_final_desc + '">' + lang.badge_proc_final + '</span>';
            
            let tr = document.createElement('tr');
            let gradeTd = isSpecificVpl ? `<td>${gradeStr}</td>` : '';
            tr.innerHTML = `
                <td>${uLink} ${badges}</td>
                <td>${st.group}</td>
                <td>${st.subs}</td>
                ${gradeTd}
                <td>${dFirst}</td>
                <td>${dLast}</td>
                <td>${totalRuns}</td>
                <td>${totalDebugs}</td>
                <td>${totalEvals}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderChart(type, datasetsInfo) {
        if (currentChart) currentChart.destroy();
        const ctx = document.getElementById('mainChart').getContext('2d');
        const chartInner = document.getElementById('chartInner');
        chartInner.style.minWidth = '100%';
        const chartWarning = document.getElementById('chartWarning');
        
        let totalSubs = datasetsInfo.reduce((acc, ds) => acc + ds.data.length, 0);
        if (totalSubs === 0) {
            document.getElementById('mainChart').style.display = 'block';
            document.getElementById('customHtmlChart').style.display = 'none';
            currentChart = new Chart(ctx, { type: 'bar', data: { labels: [lang.label_no_data], datasets: [{data:[0]}] }});
            document.getElementById('zoomControls').style.display = 'none';
            return;
        }

        if (type === 'heatmap_tiempo') {
            document.getElementById('mainChart').style.display = 'none';
            document.getElementById('customHtmlChart').style.display = 'block';
            document.getElementById('zoomControls').style.display = 'none';
            
            let matrix = Array(7).fill(0).map(() => Array(24).fill(0));
            let maxVal = 0;
            datasetsInfo.forEach(ds => {
                ds.data.forEach(s => {
                    let d = new Date(s.datesubmitted * 1000);
                    let day = d.getDay();
                    let hour = d.getHours();
                    matrix[day][hour]++;
                    if (matrix[day][hour] > maxVal) maxVal = matrix[day][hour];
                });
            });
            
            let daysArr = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            let html = '<table style=\'width:100%; border-collapse:collapse; text-align:center; font-size:12px; font-family:sans-serif;\'>';
            html += '<tr><th></th>';
            for(let h=0; h<24; h++) html += '<th style=\'padding:4px; color:#6c757d;\'>' + h + 'h</th>';
            html += '</tr>';
            
            let order = [1,2,3,4,5,6,0];
            order.forEach(dayIdx => {
                html += '<tr><td style=\'font-weight:bold; padding:8px; text-align:right; color:#495057;\'>' + daysArr[dayIdx] + '</td>';
                for(let h=0; h<24; h++) {
                    let val = matrix[dayIdx][h];
                    let intensity = maxVal > 0 ? (val / maxVal) : 0;
                    let bg = 'rgba(0, 123, 255, ' + intensity + ')';
                    let color = intensity > 0.5 ? 'white' : (val > 0 ? '#212529' : 'transparent');
                    let title = val + ' ' + lang.label_subs.toLowerCase();
                    html += '<td style=\'background:' + bg + '; color:' + color + '; padding:8px; border:1px solid #e9ecef; font-weight:bold; cursor:crosshair;\' title=\'' + title + '\'>' + (val > 0 ? val : '') + '</td>';
                }
                html += '</tr>';
            });
            html += '</table>';
            
            document.getElementById('customHtmlChart').innerHTML = html;
            return;
        }

        document.getElementById('mainChart').style.display = 'block';
        document.getElementById('customHtmlChart').style.display = 'none';
        document.getElementById('customHtmlChart').innerHTML = '';

        let zoomControls = document.getElementById('zoomControls');
        if (type === 'esfuerzo' || type === 'evolucion') zoomControls.style.display = 'flex';
        else zoomControls.style.display = 'none';

        let chartDatasets = [];
        let commonLabels = [];

        if (type === 'rendimiento') {
            let scale = getSelectedScale();
            let binKeys = [];
            if (scale === '100') {
                binKeys = ['0-20', '20-40', '40-50', '50-70', '70-90', '90-100'];
            } else if (scale === 'letters') {
                binKeys = ['F', 'D', 'C', 'B', 'A'];
            } else {
                binKeys = ['0-2', '2-4', '4-5', '5-7', '7-9', '9-10'];
            }

            let rangesList = datasetsInfo.map(ds => {
                let ranges = {};
                binKeys.forEach(k => ranges[k] = 0);
                
                let finalGrades = {};
                ds.data.forEach(s => {
                    if (s.grade === null) return;
                    let key = s.userid + '_' + s.vpl;
                    if (finalGrades[key] === undefined || s.datesubmitted > finalGrades[key].date) {
                        finalGrades[key] = { grade: s.grade, date: s.datesubmitted };
                    }
                });

                Object.values(finalGrades).forEach(g => {
                    let grade = g.grade;
                    if (scale === '100') {
                        if (grade < 0.2) ranges['0-20']++;
                        else if (grade < 0.4) ranges['20-40']++;
                        else if (grade < 0.5) ranges['40-50']++;
                        else if (grade < 0.7) ranges['50-70']++;
                        else if (grade < 0.9) ranges['70-90']++;
                        else ranges['90-100']++;
                    } else if (scale === 'letters') {
                        if (grade < 0.6) ranges['F']++;
                        else if (grade < 0.7) ranges['D']++;
                        else if (grade < 0.8) ranges['C']++;
                        else if (grade < 0.9) ranges['B']++;
                        else ranges['A']++;
                    } else {
                        if (grade < 0.2) ranges['0-2']++;
                        else if (grade < 0.4) ranges['2-4']++;
                        else if (grade < 0.5) ranges['4-5']++;
                        else if (grade < 0.7) ranges['5-7']++;
                        else if (grade < 0.9) ranges['7-9']++;
                        else ranges['9-10']++;
                    }
                });
                return ranges;
            });
            commonLabels = Object.keys(rangesList[0]);
            chartDatasets = datasetsInfo.map((ds, i) => ({
                label: lang.label_num_subs + ' (' + ds.label + ')',
                data: Object.values(rangesList[i]),
                backgroundColor: ds.color
            }));

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: commonLabels, datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, zoom: zoomOptions },
                    scales: { y: { beginAtZero: true, title: {display:true, text:lang.label_qty} }, x: {title: {display:true, text:lang.label_grade_range}} }
                }
            });

        } else if (type === 'esfuerzo') {
            chartDatasets = datasetsInfo.map(ds => {
                let userEffort = {};
                ds.data.forEach(s => {
                    if (!userEffort[s.userid]) {
                        userEffort[s.userid] = { vplMaxEffort: {} };
                    }
                    if (!userEffort[s.userid].vplMaxEffort[s.vpl]) {
                        userEffort[s.userid].vplMaxEffort[s.vpl] = { runs: 0, evals: 0 };
                    }
                    if (s.run_count > userEffort[s.userid].vplMaxEffort[s.vpl].runs) {
                        userEffort[s.userid].vplMaxEffort[s.vpl].runs = s.run_count;
                    }
                    if (s.nevaluations > userEffort[s.userid].vplMaxEffort[s.vpl].evals) {
                        userEffort[s.userid].vplMaxEffort[s.vpl].evals = s.nevaluations;
                    }
                });
                
                let scatterData = Object.keys(userEffort).map(uid => {
                    let totalR = 0, totalE = 0;
                    Object.values(userEffort[uid].vplMaxEffort).forEach(v => {
                        totalR += v.runs;
                        totalE += v.evals;
                    });
                    return { x: totalR, y: totalE, userid: uid };
                });

                return {
                    label: ds.label,
                    data: scatterData,
                    backgroundColor: ds.color + '99',
                    pointRadius: 5
                };
            });
            
            currentChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { 
                        legend: { display: false },
                        zoom: zoomOptions,
                        tooltip: { callbacks: { label: function(ctx) { 
                            let uid = ctx.raw.userid;
                            let uName = rawData.user_names_map && rawData.user_names_map[uid] ? rawData.user_names_map[uid] : uid;
                            return `${uName}: ${ctx.raw.x} ejec., ${ctx.raw.y} evals.`; 
                        } } }
                    },
                    scales: { x: { title: { display: true, text: lang.label_execs } }, y: { title: { display: true, text: lang.label_evals } } }
                }
            });

        } else if (type === 'evolucion') {
            let dateSets = datasetsInfo.map(ds => {
                let dateCounts = {};
                ds.data.forEach(s => {
                    let jsDate = new Date(s.datesubmitted * 1000);
                    let d = jsDate.getFullYear() + '-' + 
                            String(jsDate.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(jsDate.getDate()).padStart(2, '0');
                    dateCounts[d] = (dateCounts[d] || 0) + 1;
                });
                return dateCounts;
            });
            
            let allDates = new Set();
            dateSets.forEach(dc => Object.keys(dc).forEach(d => allDates.add(d)));
            commonLabels = Array.from(allDates).sort();

            chartDatasets = datasetsInfo.map((ds, i) => {
                let dataPoints = commonLabels.map(d => ({ x: d, y: dateSets[i][d] || 0 }));
                return {
                    label: ds.label,
                    data: dataPoints,
                    backgroundColor: ds.color + '99',
                    borderColor: ds.color,
                    borderWidth: 1
                };
            });

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { datasets: chartDatasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, zoom: zoomOptions },
                    scales: { x: { type: 'time', time: {unit: 'day'} }, y: { beginAtZero: true, title: {display:true, text:lang.label_subs} } }
                }
            });

        }
    }

    document.getElementById('tableSearch').addEventListener('input', function(e) {
        let term = e.target.value.toLowerCase();
        let rows = document.querySelectorAll('#dataTableBody tr');
        rows.forEach(row => {
            if (row.cells.length > 0) {
                let text = row.cells[0].textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            }
        });
    });
    document.getElementById('btnExportCSV').addEventListener('click', function() {
        let csv = [];
        let rows = document.querySelectorAll('.vpl-table tr');
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            if (i > 0 && rows[i].style.display === 'none') continue;
            
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\\r\\n|\\n|\\r)/gm, ' ').replace(/\"/g, '\"\"');
                row.push('\"' + data + '\"');
            }
            csv.push(row.join(','));
        }
        
        let csvFile = new Blob([csv.join('\\n')], {type: 'text/csv'});
        let downloadLink = document.createElement('a');
        downloadLink.download = 'vpl_analytics_export.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });
    function initWhenChartReady() {
        if (typeof Chart !== 'undefined') {
            updateDashboard();
        } else {
            setTimeout(initWhenChartReady, 50);
        }
    }
    initWhenChartReady();
});

})(window, document);
