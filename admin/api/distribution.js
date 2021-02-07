import request from '@/utils/request'
import Qs from 'qs'
export function getList(query) {
  return request({
    url: 'distribution',
    method: 'get',
    params: query
  })
}

export function create(data) {
  data = Qs.parse(data)
  return request({
    url: 'distribution',
    method: 'post',
    data
  })
}

export function edit(data) {
  data = Qs.parse(data)
  return request({
    url: 'distribution/' + data.id,
    method: 'post',
    data
  })
}

export function destroy(id) {
  return request({
    url: 'distribution/destroy/' + id,
    method: 'post'
  })
}
