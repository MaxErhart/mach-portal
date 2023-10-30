import { createStore } from 'vuex'
import axios from 'axios';



function actionDeleteFactory(identifier, {commit, state}) {
    async function action({key=null, cacheByKey=true, id=null,deep=null}={}) {
        const identifierSingle = identifier.substring(0, identifier.length - 1)
        
        var url = `${state.apiUrl}/${identifier}/${id}`
        const {data, error} = await axios({
            method: 'delete',
            url: url,
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        }).catch(error=>{
            return {data:null,error}
        })
        if(!error && data!==null) {
            commit('clearWhere', {identifier, identifierSingle, cacheByKey, key, id,deep})
        }
        return {[identifier]:data, error}
    }
    return action
}


function actionUpdateFactory(identifier, {commit, state}) {
    async function action({key=null, cacheByKey=true, formData=null, id=null,deep=null}={}) {
        const identifierSingle = identifier.substring(0, identifier.length - 1)
        
        var url = `${state.apiUrl}/${identifier}/${id}`
        const {data, error} = await axios({
            method: 'post',
            url: url,
            data: formData,
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        }).catch(error=>{
            return {data:null,error}
        })
        if(!error && data!==null) {
            commit('setWhere', {identifier, identifierSingle, [identifierSingle]:data, cacheByKey, key, id,deep})
        }
        return {[identifierSingle]:data, error}
    }
    return action
}

function actionPostFactory(identifier, {commit, state}) {
    async function action({key=null, cacheByKey=true, formData=null,deep=null}={}) {
        const identifierSingle = identifier.substring(0, identifier.length - 1)
        
        var url = `${state.apiUrl}/${identifier}`
        const {data, error} = await axios({
            method: 'post',
            url: url,
            data: formData,
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        }).catch(error=>{
            return {data:null,error}
        })
        if(!error && data!==null) {
            commit('setWhere', {identifier, identifierSingle, [identifierSingle]:data, cacheByKey, key, id: data.id,deep})
        }
        return {[identifierSingle]:data, error}
    }
    return action
}


function refreshFactory(identifier, dispatch) {
    async function refresh({id=null,cacheByKey=true,key=null,get=null,deep=null}={}) {
        const data = await dispatch(identifier, {id,cacheByKey,key,get,refreshData:true,deep})
        return data
    }
    return refresh
}
function actionGetFactory(identifier, {dispatch, commit, state}) {
    async function action({key=null,id=null,get=null,cacheByKey=true,refreshData=false,deep=null}={}) {

        const refresh = refreshFactory(identifier, dispatch)
        const hasId = id!==null&&id!==undefined
        const identifierSingle = identifier.substring(0, identifier.length - 1)
        id = parseInt(id)
        
        if(hasId && cacheByKey && !refreshData) {
            const index1 = state[identifier].dataByKey[key]?.findIndex(obj=>obj?.id===id)
            if(index1>=0) {
                return {[identifierSingle]: state[identifier].dataByKey[key][index1], refresh, error: null}
            }

            const singleExists1 = key in state[identifierSingle].dataByKey && id in state[identifierSingle].dataByKey[key]
            if(singleExists1) {
                return {[identifierSingle]: state[identifierSingle].dataByKey[key][id], refresh, error: null}
            }
        }

        if(hasId && !cacheByKey && !refreshData) {
            const index2 = state[identifier].prevData.findIndex(obj=>obj.id===id)
            if(index2>=0 && state[identifier].prevKey===key) {
                return {[identifierSingle]: state[identifier].prevData[index2], refresh, error: null}
            }
            
            const singleExists2 = state[identifierSingle].prevKey[id]===key && state[identifierSingle].firstFetch[id]
            if(singleExists2) {
                return {[identifierSingle]: state[identifierSingle].prevData[id], refresh, error: null}
            }
        }

        if(!hasId && cacheByKey && key in state[identifier].dataByKey && !refreshData) {
            return {[identifier]: state[identifier].dataByKey[key], refresh, error: null}
        }

        if(!hasId && !cacheByKey && state[identifier].firstFetch && state[identifier].prevKey===key && !refreshData) {
            return {[identifier]: state[identifier].prevData, refresh, error: null}
        } 

        var url = `${state.apiUrl}/${identifier}`
        if(hasId) {
            url+=`/${id}`
        }
        if(get) {
            Object.keys(get).forEach((key,index)=>{
                if(index===0) {
                    url+=`?${key}=${get[key]}`
                    return
                }
                url+=`&${key}=${get[key]}`
            })
        }
        const {data, error} = await axios({
            method: 'get',
            url: url
        }).catch(error=>{
            return {data:null,error}
        })
        if(!error && data!==null) {
            if(hasId) {
                commit('setWhere', {identifier, identifierSingle, [identifierSingle]:data, cacheByKey, key, id,deep})
            } else {
                commit('set', {identifier, [identifier]:data, cacheByKey, key})
            }
        }
        if(hasId) {
            return {[identifierSingle]:data, refresh, error}
        } else {
            return {[identifier]:data, refresh, error}
        }
    }
    return action
}


export default createStore({
    state: {
        navItemMainHeight: 40,
        selectionsData: [],
        formSubmissionData: {},
        loggedIn: false,
        loginFormActive: false,
        userInformation: null,
        // baseUrl: 'https://www-3.mach.kit.edu/dist/',
        baseUrl: 'https://www-3.mach.kit.edu/dist/#',
        baseFileUrl: 'https://www-3.mach.kit.edu/dfiles/',
        apiUrl: 'https://www-3.mach.kit.edu/api/public/index.php/api/',
        // apiUrl: 'https://www-3.mach.kit.edu/api/shib/mach-api/public/index.php/api',
        apiAuthUrl: 'https://www-3.mach.kit.edu/api/public/index.php/api/auth/',
        // apiAuthUrl: 'https://www-3.mach.kit.edu/api/shib/mach-api/public/index.php/api/auth/',
        apps: null,
        settings: null,

        profile: null,
        userFetched: false,
        isAuthenticated: true,

        sideNavigationOn: true,
        screenWidth: 0,
        sideNavigationWidth: 0,
        users: {
            dataByKey: {},
            prevKey: null,
            prevData: null,
            firstFetch: false,
        },
        user: {
            dataByKey: {},
            prevKey: {},
            prevData: {},
            firstFetch: {},
        },

        actions: {
            dataByKey: {},
            prevKey: null,
            prevData: null,
            firstFetch: false,
        },
        action: {
            dataByKey: {},
            prevKey: {},
            prevData: {},
            firstFetch: {},
        },

        groups: {
            dataByKey: {},
            prevKey: null,
            prevData: null,
            firstFetch: false,
        },
        group: {
            dataByKey: {},
            prevKey: {},
            prevData: {},
            firstFetch: {},
        },

        forms: {
            dataByKey: {},
            prevKey: null,
            prevData: null,
            firstFetch: false,
        },
        form: {
            dataByKey: {},
            prevKey: {},
            prevData: {},
            firstFetch: {},
        },

        submissions: {
            dataByKey: {},
            prevKey: null,
            prevData: null,
            firstFetch: false,
        },
        submission: {
            dataByKey: {},
            prevKey: {},
            prevData: {},
            firstFetch: {},
        },
        _forms: {
            data_index_by_id: {},
            data: [],
            fetched_all_forms: false,
        },
        _submissions: {
            data_by_form_id: {},
            data_index_by_id: {},
            fetched_all_by_form_id: {},
            form_id_refs_by_form_id: {},
        },
        archive: {

        }
    },
    mutations: {
        setScreenWidth(state, payload) {
            state.screenWidth = payload
        },
        setSideNavigationOn(state, payload) {
            state.sideNavigationOn = payload
        },
        setSideNavigationWidth(state, payload) {
            state.sideNavigationWidth = payload
        },
        clear(state) {
            state.users = {
                dataByKey: {},
                prevKey: null,
                prevData: null,
                firstFetch: false,
            }
            state.user = {
                dataByKey: {},
                prevKey: {},
                prevData: {},
                firstFetch: {},
            }
    
            state.actions = {
                dataByKey: {},
                prevKey: null,
                prevData: null,
                firstFetch: false,
            }
            state.action = {
                dataByKey: {},
                prevKey: {},
                prevData: {},
                firstFetch: {},
            }

            state.groups = {
                dataByKey: {},
                prevKey: null,
                prevData: null,
                firstFetch: false,
            }
            state.group = {
                dataByKey: {},
                prevKey: {},
                prevData: {},
                firstFetch: {},
            }

            state.forms = {
                dataByKey: {},
                prevKey: null,
                prevData: null,
                firstFetch: false,
            }
            state.form = {
                dataByKey: {},
                prevKey: {},
                prevData: {},
                firstFetch: {},
            }

            state.submissions = {
                dataByKey: {},
                prevKey: null,
                prevData: null,
                firstFetch: false,
            }
            state.submission = {
                dataByKey: {},
                prevKey: {},
                prevData: {},
                firstFetch: {},
            }
            state.archive = {

            }
        },
        set(state, payload) {
            const identifier = payload.identifier
            if(payload.cacheByKey) {
                state[identifier].dataByKey[payload.key] = payload[identifier]
            }
            state[identifier].prevData = payload[identifier]
            state[identifier].prevKey = payload.key
            state[identifier].firstFetch = true
        },
        clearWhere(state, payload) {
            const deep = payload.deep


            // const identifier = payload.identifier
            const identifierSingle = payload.identifierSingle

            if(deep) {
                if(state[identifierSingle].dataByKey?.[payload.key]?.[payload.id]) {
                    delete state[identifierSingle].dataByKey[payload.key][payload.id]
                }
                if(state[identifierSingle].prevData[deep]?.[payload.id]) {
                    delete state[identifierSingle].prevData[deep][payload.id]
                }
                if(state[identifierSingle].prevKey[payload.id]) {
                    delete state[identifierSingle].prevKey[payload.id]
                }
                if(state[identifierSingle].firstFetch[payload.id]) {
                    delete state[identifierSingle].firstFetch[payload.id]
                }

                const index1 = state[payload.identifier].dataByKey?.[payload.key][deep].findIndex(obj=>obj.id===payload.id)
                if(index1>=0) {
                    state[payload.identifier].dataByKey[payload.key][deep].splice(index1, 1)
                }
                if(state[payload.identifier].prevKey===payload.key && state[payload.identifier].firstFetch) {
                    const index2 = state[payload.identifier].prevData[deep]?.findIndex(obj=>obj.id===payload.id)
                    if(index2>=0) {
                        state[payload.identifier].prevData[deep]?.splice(index2, 1)
                    }
                }
                
                

            } else {
                if(state[identifierSingle].dataByKey?.[payload.key]?.[payload.id]) {
                    delete state[identifierSingle].dataByKey[payload.key][payload.id]
                }
                if(state[identifierSingle].prevData?.[payload.id]) {
                    delete state[identifierSingle].prevData[payload.id]
                }
                if(state[identifierSingle].prevKey[payload.id]) {
                    delete state[identifierSingle].prevKey[payload.id]
                }
                if(state[identifierSingle].firstFetch[payload.id]) {
                    delete state[identifierSingle].firstFetch[payload.id]
                }
                const index1 = state[payload.identifier].dataByKey[payload.key].findIndex(obj=>obj.id===payload.id)
                if(index1>=0) {
                    state[payload.identifier].dataByKey[payload.key].splice(index1, 1)
                }
                if(state[payload.identifier].prevKey===payload.key && state[payload.identifier].firstFetch) {
                    const index2 = state[payload.identifier].prevData?.findIndex(obj=>obj.id===payload.id)
                    if(index2>=0) {
                        state[payload.identifier].prevData?.splice(index2, 1)
                    }
                } 
            }

        },
        setWhere(state, payload) {
            const deep = payload.deep
            const identifier = payload.identifier
            const identifierSingle = payload.identifierSingle
            if(payload.cacheByKey) {
                if(!(payload.key in state[identifierSingle].dataByKey)) {
                    state[identifierSingle].dataByKey[payload.key] = {}
                }
                state[identifierSingle].dataByKey[payload.key][payload.id] = payload[identifierSingle]

                if(payload.key in state[identifier].dataByKey) {
                    var index = -1
                    if(deep) {
                        index = state[identifier].dataByKey[payload.key][deep].findIndex(obj=>obj.id===payload.id)
                        if(index>=0) {
                            state[identifier].dataByKey[payload.key][deep][index] = payload[identifierSingle]
                        } else {
                            state[identifier].dataByKey[payload.key][deep].push(payload[identifierSingle])
                        }
                    } else {
                        index = state[identifier].dataByKey[payload.key]?.findIndex(obj=>obj.id===payload.id)
                        if(index>=0) {
                            state[identifier].dataByKey[payload.key][index] = payload[identifierSingle]
                        } else {
    
                            state[identifier].dataByKey[payload.key].push(payload[identifierSingle])
                        }
                    }

                }
            }
            state[identifierSingle].prevKey[payload.id] = payload.key
            state[identifierSingle].prevData[payload.id] = payload[identifierSingle]
            state[identifierSingle].firstFetch[payload.id] = true

            if(state[identifier].prevKey===payload.key && state[identifier].firstFetch) {
                var index2 = -1
                if(deep) {
                    index2 = state[identifier].prevData[deep].findIndex(obj=>obj.id===payload.id)
                    if(index2>=0) {
                        state[identifier].prevData[deep][index2] = payload[identifierSingle]
                    } else {
                        state[identifier].prevData[deep].push(payload[identifierSingle])
                    }
                } else {
                    index2 = state[identifier].prevData?.findIndex(obj=>obj.id===payload.id)
                    if(index2>=0) {
                        state[identifier].prevData[index2] = payload[identifierSingle]
                    } else {
                        state[identifier].prevData.push(payload[identifierSingle])
                    }
                }
            }
        },
        profile(state, payload) {
            state.profile=payload
        },
        logout(state) {
            state.loggedIn = false;
            state.profile = null;
            state.apps = null;
            state.settings = null;            
        },
        set_forms(state,payload) {
            state._forms.data=payload
            state._forms.fetched_all_forms=true
            payload.forEach((form,index) => {
                state._forms.data_index_by_id[form.id] = index
            })
        },
        push_form(state,payload) {
            if(payload.id in state._forms.data_index_by_id) {
                state._forms.data[state._forms.data_index_by_id[payload.id]] = payload
            } else {
                state._forms.data.push(payload)
                state._forms.data_index_by_id[payload.id] = state._forms.data.length-1
            }

        },
        delete_form_by_id(state,payload) {
            if(payload in state._forms.data_index_by_id) {
                state._forms.data.splice(state._forms.data_index_by_id[payload], 1)
                delete state._forms.data_index_by_id[payload]
            }
        },
        set_submissions(state,payload) {

            state._submissions.form_id_refs_by_form_id[payload.form_id] = Object.keys(payload.submissions)
            Object.keys(payload.submissions).forEach(form_id=>{
                state._submissions.form_id_refs_by_form_id[form_id] = Object.keys(payload.submissions)
            })
            Object.keys(payload.submissions).forEach(form_id=>{
                state._submissions.data_by_form_id[form_id] = payload.submissions[form_id]
                state._submissions.fetched_all_by_form_id[form_id] = true
                payload.submissions[form_id].forEach((submission,index)=>{
                    state._submissions.data_index_by_id[submission.id] = index
                })
            })
        },
        push_submissions(state,payload) {
            state._submissions.data_by_form_id[payload.form_id] = payload.submissions
            payload.submissions.forEach((submission,index)=>{
                state._submissions.data_index_by_id[submission.id] = index
            })
        },
        push_submission(state,payload) {
            if(payload.submission?.id in state._submissions.data_index_by_id) {
                state._submissions.data_by_form_id[payload.form_id][state._submissions.data_index_by_id[payload.submission.id]] = payload.submission
            } else {
                if(payload.form_id in state._submissions.data_by_form_id) {
                    state._submissions.data_by_form_id[payload.form_id].push(payload.submission)
                    state._submissions.data_index_by_id[payload.submission.id] = state._submissions.data_by_form_id[payload.form_id].length-1
                } else {
                    state._submissions.data_by_form_id[payload.form_id] = [payload.submission]
                    state._submissions.data_index_by_id[payload.submission.id] = 0
                }
            }
        },
        delete_submission_by_id(state,payload) {
            if(payload.submission_id in state._submissions.data_index_by_id) {
                state._submissions.data_by_form_id[payload.form_id].splice(state._submissions.data_index_by_id[payload.submission_id], 1)
                delete state._submissions.data_index_by_id[payload.submission_id]
                state._submissions.data_by_form_id[payload.form_id].forEach((submission,index)=>{
                    state._submissions.data_index_by_id[submission.id] = index
                })
            }
        },
        add_reply(state,payload) {
            if(payload.reply.submission_id in state._submissions.data_index_by_id) {
                state._submissions.data_by_form_id[payload.form_id][state._submissions.data_index_by_id[payload.reply.submission_id]].replies.push(payload.reply)
            }
        },
        set_archive_submissions(state,payload) {
            state.archive[payload.key] = payload.submissions
        },
        dearchive_submissions(state,payload) {
            payload.submissions.forEach(submission=>{
                if(submission.archive_group in state.archive) {
                    state.archive[submission.archive_group]=state.archive[submission.archive_group].filter(archive_submission=>archive_submission.id!=submission.id)
                }
            })
            state._forms.data[state._forms.data_index_by_id[payload.form_id]].archive_groups = payload.archive_groups

        },
        archive_submissions(state,payload) {
            payload.archived.forEach(submission=>{
                if(submission.id in state._submissions.data_index_by_id) {
                    if(submission.id in state._submissions.data_index_by_id) {
                        state._submissions.data_by_form_id[submission.form_id].splice(state._submissions.data_index_by_id[submission.id], 1)
                        delete state._submissions.data_index_by_id[submission.id]
                        state._submissions.data_by_form_id[submission.form_id].forEach((submission,index)=>{
                            state._submissions.data_index_by_id[submission.id] = index
                        })
                    }
                }
            })
            state.archive[payload.key]=payload.archive
            state._forms.data[state._forms.data_index_by_id[payload.form_id]].archive_groups = payload.archive_groups

        }
    },
    actions: {
        async _replies({commit,state},{method='get',submission_id=null,formData=null,form_id=null}) {
            if(method=='post') {
                var url = `${state.apiUrl}/reply/${submission_id}`

                const {data, error} = await axios({
                    method,
                    url,
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!=null) {
                    commit('add_reply', {reply:data,form_id})
                }
                const submissions = {}

                state._submissions.form_id_refs_by_form_id[form_id].forEach(ref_id=>{
                    submissions[ref_id] = state._submissions.data_by_form_id[ref_id]
                })
                return {
                    submissions: JSON.parse(JSON.stringify(submissions)),
                    error: error,
                }
            }
        },
        // Important note: make sure local component variables and store variables do not point to the same object !!!!
        async _archive({commit,state},{method='get',key=null,archive=true,formData=null,form_id,}) {
            var url = `${state.apiUrl}/_forms/submissions`
            if(method=='get') {
                url += `/archive/${form_id}`
                if(key!==null) {
                    url += `/${key}`
                }
                const {data,error} = await axios({
                    method: 'get',
                    url: url,
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!==null) {
                    commit('set_archive_submissions', {submissions:data,key})
                    return {submissions:JSON.parse(JSON.stringify(data)), error,key}
                }
                return {submissions:null,error,key}
            } else if(method=='post') {
                if(archive) {
                    url += '/archive'
                } else {
                    url += '/dearchive'
                }
                const {data,error} = await axios({
                    method: 'post',
                    url: url,
                    data: formData
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!==null) {
                    const submissions = {}


                    if(archive) {
                        commit('archive_submissions', {archive:data.archive,archived: data.archived,key:data.archive_group,archive_groups:data.archive_groups,form_id})
                        state._submissions.form_id_refs_by_form_id[form_id].forEach(ref_id=>{
                            submissions[ref_id] = state._submissions.data_by_form_id[ref_id]
                        })
                        return {archive_groups: JSON.parse(JSON.stringify(data.archive_groups)),submissions: JSON.parse(JSON.stringify(submissions)),archive:JSON.parse(JSON.stringify(state.archive)),error}
                    } else {

                        commit('dearchive_submissions', {submissions:data.dearchived,archive_groups:data.archive_groups,form_id})
                        commit('push_submissions', {submissions:data.live,form_id})
                        state._submissions.form_id_refs_by_form_id[form_id].forEach(ref_id=>{
                            submissions[ref_id] = state._submissions.data_by_form_id[ref_id]
                        })
                        return {archive_groups: JSON.parse(JSON.stringify(data.archive_groups)),submissions: JSON.parse(JSON.stringify(submissions)),archive:JSON.parse(JSON.stringify(state.archive)),error}
                    }
                }
                return {submissions: null, error,key}
            }

        },
        async _submissions({commit,state}, {method='get',form_id,submission_id=null,formData=null,anon=false}) {
            var url = `${state.apiUrl}/_forms/${form_id}/submissions`
            if(anon) {
                url = `${state.apiUrl}/_forms/anon/${form_id}/submissions`
            }
            if(method!='post' && submission_id!==null) {
                url += `/${submission_id}`
            }
            if(method=='get') {
                if(submission_id==null) {
                    if(form_id in state._submissions.fetched_all_by_form_id) {
                        const submissions = {}

                        state._submissions.form_id_refs_by_form_id[form_id].forEach(ref_id=>{
                            submissions[ref_id] = state._submissions.data_by_form_id[ref_id]
                        })
                        return {
                            form: JSON.parse(JSON.stringify(state._forms.data[state._forms.data_index_by_id[form_id]])),
                            submissions: JSON.parse(JSON.stringify(submissions)),
                            error: null,
                        }
                    }
                    const {data, error} = await axios({
                        method: method,
                        url: url
                    }).catch(error=>{

                        return {data:null,error}
                    })
                    if(!error && data!==null) {
                        commit('push_form', data.form)
                        commit('set_submissions', {form_id, submissions:data.submissions})
                        return {form: JSON.parse(JSON.stringify(data?.form)), submissions:JSON.parse(JSON.stringify(data?.submissions)), error}
                    }
                    
                    return {form: null, submissions:null, error}
                } else if(submission_id in state._submissions.data_index_by_id && form_id in state._forms.data_index_by_id) {
                    return {
                        form: JSON.parse(JSON.stringify(state._forms.data[state._forms.data_index_by_id[form_id]])),
                        submission: JSON.parse(JSON.stringify(state._submissions.data_by_form_id[form_id][form_id][state._submissions.data_index_by_id[submission_id]])),
                        error:null,
                    }
                } else {
                    const {data, error} = await axios({
                        method: method,
                        url: url
                    }).catch(error=>{
                        return {data:null,error}
                    })
                    if(!error && data!=null) {
                        commit('push_form', data.form)
                        commit('push_submission', {form_id, submission:data.submission})
                        return {form: JSON.parse(JSON.stringify(data?.form)), submission:JSON.parse(JSON.stringify(data?.submission)), error}
                    }
                    return {form: null, submission:null, error}
                }
            } else if(method=='post') {
                const {data, error} = await axios({
                    method: method,
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!=null) {
                  commit('push_submission', {form_id, submission:data.submission})
                  return {form: JSON.parse(JSON.stringify(data?.form)), submission:JSON.parse(JSON.stringify(data?.submission)), error}
                }
                return {form:null, submission:null, error}
            } else if(method=='update') {
                const {data, error} = await axios({
                    method: 'post',
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!=null) {
                    commit('push_submission', {form_id, submission:data.submission})
                    return {form: JSON.parse(JSON.stringify(data?.form)), submission:JSON.parse(JSON.stringify(data?.submission)), error}
                }
                return {form:null,submission:null,error}
            } else if(method=='delete') {
                const {error} = await axios({
                    method: method,
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error) {
                    commit('delete_submission_by_id', {form_id, submission_id})
                    return {submissions:JSON.parse(JSON.stringify(state._submissions.data_by_form_id[form_id])),error}

                }
                return {submissions:null,error}
            }


        },
        async _forms({commit,state}, {method='get',form_id=null,formData=null}) {
            var url = `${state.apiUrl}/_forms`

            if(method!='post' && form_id!=null) {
                url += `/${form_id}`
            }
            if(method=='get') {
                if(form_id==null) {
                    if(state._forms.fetched_all_forms) {
                        return {forms: JSON.parse(JSON.stringify(state._forms.data)), error: null}
                    }
                    const {data, error} = await axios({
                        method: method,
                        url: url
                    }).catch(error=>{
                        return {data:null,error}
                    })
                    if(!error && data!=null) {
                        commit('set_forms', data)
                        return {forms:JSON.parse(JSON.stringify(data)), error}
                    }
                    return {forms:null, error}
                } else if(form_id in state._forms.data_index_by_id) {
                    return {form: JSON.parse(JSON.stringify(state._forms.data[state._forms.data_index_by_id[form_id]])), error: null}
                } else {
                    const {data, error} = await axios({
                        method: method,
                        url: url
                    }).catch(error=>{
                        return {data:null,error}
                    })
                    if(!error && data!=null) {
                        commit('push_form', data)
                        return {form:JSON.parse(JSON.stringify(data)), error}
                    }
                    return {form:null, error}

                }
            } else if(method=='post') {
                // url = `${state.apiUrl}/testing-the-api`
                const {data, error} = await axios({
                    method: method,
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!=null) {
                    commit('push_form', data)
                    return {form:JSON.parse(JSON.stringify(data)), error}
                }
                return {form:null, error}
            } else if(method=='update') {
                const {data, error} = await axios({
                    method: 'post',
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error && data!=null) {
                    commit('push_form', data)
                    return {form:JSON.parse(JSON.stringify(data)), error}
                }
                return {form:null, error}
            } else if(method=='delete') {
                const {error} = await axios({
                    method: method,
                    url: url,
                    data: formData,
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error) {
                    commit('delete_form_by_id', form_id)
                    return {forms:JSON.parse(JSON.stringify(state._forms.data)), error}
                }
                return {forms:null, error}
            } else if(method=='copy') {
                const {data,error} = await axios({
                    method: 'get',
                    url: state.apiUrl + '/forms/copy/' + form_id
                }).catch(error=>{
                    return {data:null,error}
                })
                if(!error) {
                    commit('push_form', data)
                    return {form:JSON.parse(JSON.stringify(data)), error}
                }
                return {form:null, error}
            }
        },
        async actions({dispatch, commit, state}, {key,id,get,method='get',cacheByKey,formData,refreshData}={}) {
            if(method==='get') {
                if(!state.actionsGetAction) {
                    state.actionsGetAction = actionGetFactory('actions', {dispatch, commit, state})
                }
                const data = await state.actionsGetAction({key,id,get,cacheByKey,refreshData})
                return data
            }
            if(method==='post') {
                if(!state.actionsPostAction) {
                    state.actionsPostAction = actionPostFactory('actions', { commit, state})
                }
                const data = await state.actionsPostAction({key,get,cacheByKey,formData})
                return data
            }
            if(method==='update') {
                if(!state.actionsUpdateAction) {
                    state.actionsUpdateAction = actionUpdateFactory('actions', {commit, state})
                }
                const data = await state.actionsUpdateAction({key,id,get,cacheByKey,formData})
                return data
            }
            if(method==='delete') {
                if(!state.actionsDeleteAction) {
                    state.actionsDeleteAction = actionDeleteFactory('actions', {commit, state})
                }
                const data = await state.actionsDeleteAction({key,id,get,cacheByKey})
                return data
            }
        },
        async groups({dispatch, commit, state}, {key,id,get,method='get',cacheByKey,formData,refreshData}={}) {
            if(method==='get') {
                if(!state.groupsGetAction) {
                    state.groupsGetAction = actionGetFactory('groups', {dispatch, commit, state})
                }
                const data = await state.groupsGetAction({key,id,get,cacheByKey,refreshData})
                return data
            }
            if(method==='post') {
                if(!state.groupsPostAction) {
                    state.groupsPostAction = actionPostFactory('groups', { commit, state})
                }
                const data = await state.groupsPostAction({key,get,cacheByKey,formData})
                return data
            }
            if(method==='update') {
                if(!state.groupsUpdateAction) {
                    state.groupsUpdateAction = actionUpdateFactory('groups', {commit, state})
                }
                const data = await state.groupsUpdateAction({key,id,get,cacheByKey,formData})
                return data
            }
            if(method==='delete') {
                if(!state.groupsDeleteAction) {
                    state.groupsDeleteAction = actionDeleteFactory('groups', {commit, state})
                }
                const data = await state.groupsDeleteAction({key,id,get,cacheByKey})
                return data
            }
        },
        async users({dispatch, commit, state}, {key,id,get,method='get',cacheByKey,formData,refreshData}={}) {
            if(method==='get') {
                if(!state.usersGetAction) {
                    state.usersGetAction = actionGetFactory('users', {dispatch, commit, state})
                }
                const data = await state.usersGetAction({key,id,get,cacheByKey,refreshData})
                return data
            }
            if(method==='post') {
                if(!state.usersPostAction) {
                    state.usersPostAction = actionPostFactory('users', { commit, state})
                }
                const data = await state.usersPostAction({key,get,cacheByKey,formData})
                return data
            }
            if(method==='update') {
                if(!state.usersUpdateAction) {
                    state.usersUpdateAction = actionUpdateFactory('users', {commit, state})
                }
                const data = await state.usersUpdateAction({key,id,get,cacheByKey,formData})
                return data
            }
            if(method==='delete') {
                if(!state.usersDeleteAction) {
                    state.usersDeleteAction = actionDeleteFactory('users', {commit, state})
                }
                const data = await state.usersDeleteAction({key,id,get,cacheByKey})
                return data
            }
            
        },
        async forms({dispatch, commit, state}, {key,id,get,method='get',cacheByKey,formData,refreshData}={}) {
            if(method==='get') {
                if(!state.formsGetAction) {
                    state.formsGetAction = actionGetFactory('forms', {dispatch, commit, state})
                }
                const data = await state.formsGetAction({key,id,get,cacheByKey,refreshData})
                return data
            }
            if(method==='post') {
                if(!state.formsPostAction) {
                    state.formsPostAction = actionPostFactory('forms', { commit, state})
                }
                const data = await state.formsPostAction({key,get,cacheByKey,formData})
                return data
            }
            if(method==='update') {
                if(!state.formsUpdateAction) {
                    state.formsUpdateAction = actionUpdateFactory('forms', {commit, state})
                }
                const data = await state.formsUpdateAction({key,id,get,cacheByKey,formData})
                return data
            }
            if(method==='delete') {
                if(!state.formsDeleteAction) {
                    state.formsDeleteAction = actionDeleteFactory('forms', {commit, state})
                }
                const data = await state.formsDeleteAction({key,id,get,cacheByKey})
                return data
            }
        },
        async submissions({dispatch, commit, state}, {key,id,get,method='get',cacheByKey,formData,refreshData,deep}={}) {
            if(method==='get') {
                if(!state.submissionsGetAction) {
                    state.submissionsGetAction = actionGetFactory('submissions', {dispatch, commit, state})
                }
                const data = await state.submissionsGetAction({key,id,get,cacheByKey,refreshData,deep})
                return data
            }
            if(method==='post') {
                if(!state.submissionsPostAction) {
                    state.submissionsPostAction = actionPostFactory('submissions', { commit, state})
                }
                const data = await state.submissionsPostAction({key,get,cacheByKey,formData,deep})
                return data
            }
            if(method==='update') {
                if(!state.submissionsUpdateAction) {
                    state.submissionsUpdateAction = actionUpdateFactory('submissions', {commit, state})
                }
                const data = await state.submissionsUpdateAction({key,id,get,cacheByKey,formData,deep})
                return data
            }
            if(method==='delete') {
                if(!state.submissionsDeleteAction) {
                    state.submissionsDeleteAction = actionDeleteFactory('submissions', {commit, state})
                }
                const data = await state.submissionsDeleteAction({key,id,get,cacheByKey,deep})
                return data
            }
        },
    },
    modules: {
    },
    getters: {
        getScreenWidth(state) {
            return state.screenWidth
        },
        getSideNavigationOn(state) {
            return state.sideNavigationOn
        },
        getSideNavigationWidth(state) {
            return state.sideNavigationWidth
        },
        getProfile(state) {
            return state.profile
        },
        getBaseUrl(state){
            return state.baseUrl;
        },
        getBaseFileUrl(state){
            return state.baseFileUrl;
        },
        getApiUrl(state){
            return state.apiUrl;
        },
        getApiAuthUrl(state){
            return state.apiAuthUrl;
        },        
    }
})
