<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button v-permission="$store.jurisdiction.DistributionCreate" class="filter-item" style="margin-left: 10px;float:right;" type="primary" icon="el-icon-edit" @click="handleCreate">添加</el-button>
    </div>

    <el-table
      v-loading="listLoading"
      ref="multipleTable"
      :key="tableKey"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange">
      <el-table-column type="expand" >
        <template slot-scope="scope">
          <el-table
            :data="scope.row.distribution_rule"
            border
            style="width: 610px">
            <el-table-column
              prop="level"
              label="级别"/>
            <el-table-column
              prop="name"
              label="别名"/>
            <el-table-column
              prop="type"
              label="返佣方式"
              width="200"/>
            <el-table-column
              prop="price"
              label="返佣值"
              width="200">
              <template slot-scope="sope">
                {{ sope.row.price }}{{ sope.row.type === '按比例' ? '%' : '' }}
              </template>
            </el-table-column>
          </el-table>
        </template>
      </el-table-column>
      <el-table-column label="编号" sortable="custom" prop="id" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="分销名称">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="分销标识">
        <template slot-scope="scope">
          <code>{{ scope.row.identification }}</code>
        </template>
      </el-table-column>
      <el-table-column label="分销等级" sortable="custom" prop="level">
        <template slot-scope="scope">
          <span>{{ scope.row.level }}级</span>
        </template>
      </el-table-column>
      <el-table-column label="分销状态" sortable="custom" prop="state">
        <template slot-scope="scope">
          <span>{{ scope.row.state === 1 ? '关闭' : '开启' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" align="center" sortable="custom" prop="created_at">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" class-name="small-padding fixed-width" width="120" fixed="right">
        <template slot-scope="scope">
          <el-tooltip v-permission="$store.jurisdiction.DistributionEdit" class="item" effect="dark" content="编辑" placement="top-start">
            <el-button type="primary" icon="el-icon-edit" circle @click="handleUpdate(scope.row)"/>
          </el-tooltip>
          <el-tooltip v-permission="$store.jurisdiction.DistributionDestroy" class="item" effect="dark" content="删除" placement="top-start">
            <el-button :loading="formLoading" type="danger" icon="el-icon-delete" circle @click="handleDelete(scope.row)"/>
          </el-tooltip>
        </template>
      </el-table-column>
    </el-table>

    <!--分页-->
    <div class="pagination-operation">
      <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" class="pagination" @pagination="getList"/>
    </div>

    <!--添加-->
    <el-dialog :title="textMap[dialogStatus]" :close-on-click-modal="false" :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="120px" style="margin-left:50px;">
        <el-form-item label="分销名称" prop="name">
          <el-input v-model="temp.name" maxlength="30" clearable/>
        </el-form-item>
        <el-form-item label="分销标识" prop="identification">
          <el-input v-model="temp.identification" maxlength="50" placeholder="英文加下划线，后台通过些标识获取配置信息" clearable/>
        </el-form-item>
        <el-form-item label="分销级别" prop="level">
          <el-radio-group v-model="temp.level" @change="setLevel">
            <el-radio :label="1">一级</el-radio>
            <!--<el-radio :label="2">二级</el-radio>
            <el-radio :label="3">三级</el-radio>-->
          </el-radio-group>
          <div slot="tip" class="el-upload__tip">支持的分销级别，最大支持三级</div>
        </el-form-item>
        <el-form-item label="分销状态" prop="level">
          <el-radio-group v-model="temp.state">
            <el-radio :label="0">开启</el-radio>
            <el-radio :label="1">关闭</el-radio>
          </el-radio-group>
          <div slot="tip" class="el-upload__tip">状态：关闭后，将不会触发</div>
        </el-form-item>
        <el-table
          :data="temp.distribution_rule"
          border
          style="width: 100%">
          <el-table-column
            prop="level"
            label="级别"
            width="140">
            <template slot-scope="scope">
              <el-form :model="scope.row">
                {{ scope.row.level }}
              </el-form>
            </template>
          </el-table-column>
          <el-table-column
            prop="name"
            label="别名"
            width="140">
            <template slot-scope="scope">
              <el-form :model="scope.row">
                <el-form-item prop="name">
                  <el-input v-model="scope.row.name" maxlength="30" size="mini" placeholder="请输入别名" clearable @input="e => updateInput(e, scope.$index, 'name')"/>
                </el-form-item>
              </el-form>
            </template>
          </el-table-column>
          <el-table-column
            prop="type"
            label="返佣方式">
            <template slot-scope="scope">
              <el-form :model="scope.row">
                <el-form-item prop="type">
                  <el-switch
                    v-model="scope.row.type"
                    active-text="按比例"
                    inactive-text="按固定值"
                    @change="e => updateInput(e, scope.$index, 'type')"/>
                </el-form-item>
              </el-form>
            </template>
          </el-table-column>
          <el-table-column
            prop="price"
            label="返佣值"
            width="140">
            <template slot-scope="scope">
              <el-form :model="scope.row">
                <el-form-item prop="price">
                  <el-input v-model="scope.row.price" maxlength="10" size="mini" placeholder="保留小数点后两位" clearable @input="e => updateInput(e, scope.$index, 'price')"/>
                </el-form-item>
              </el-form>
            </template>
          </el-table-column>
        </el-table>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">{{ $t('usuel.cancel') }}</el-button>
        <el-button :loading="formLoading" type="primary" @click="dialogStatus==='create'?create():edit()">确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<style rel="stylesheet/scss" lang="scss">
  .timeInterval{
    top:-4px;
  }
  .pagination-operation{
    margin-bottom: 80px;
    float:left;
  }
  .pagination-operation .operation{
    margin-left:20px;
    margin-top: 32px;
    font-size: 12px;
    float:left;
    margin-right: 10px;
  }
  .pagination-operation .pagination{
    float:left;
    padding: 0 0;
  }
  .drawing img{
    float:left;
  }
  .drawing .right{
    text-align: left;
    float:left;
    margin-left: 10px;
  }

  .avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .avatar-uploader .el-upload:hover {
    border-color: #409EFF;
  }
  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 288px;
    height: 188px;
    line-height: 188px;
    text-align: center;
  }
  .progress-img{
    padding: 30px;
  }
  .avatar {
    width: 288px;
    height: 188px;
    display: block;
  }
</style>

<script>
import { getList, create, edit, destroy } from '@/api/distribution'
import Pagination from '@/components/Pagination'

export default {
  name: 'DistributionList',
  components: { Pagination },
  data() {
    return {
      formLoading: false,
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() < Date.now() - 8.64e7
        }
      },
      tableKey: 0,
      list: null,
      total: 0,
      type: [
        {
          name: '满减优惠券',
          value: 1
        },
        {
          name: '随机优惠券',
          value: 2
        },
        {
          name: '折扣优惠券',
          value: 3
        }
      ],
      textMap: {
        update: '修改',
        create: '添加'
      },
      listLoading: false,
      dialogStatus: '',
      dialogFormVisible: false,
      listQuery: {
        page: 1,
        limit: 10,
        sort: '+id',
        activeIndex: '1'
      },
      temp: {},
      rules: {
        name: [
          { required: true, message: '请输入分销名称', trigger: 'blur' }
        ],
        identification: [
          { required: true, message: '请选择分销标识', trigger: 'change' }
        ],
        level: [
          { required: true, message: '请选择分销级别', trigger: 'change' }
        ]
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.listLoading = true
      getList(this.listQuery).then(response => {
        this.list = response.data.data
        this.total = response.data.total
        this.listLoading = false
      })
    },
    handleFilter() {
      this.getList()
    },
    handleUpdate(row) { // 编辑
      row.distribution_rule.forEach((item, index) => {
        row.distribution_rule[index].type = item.type === '按比例'
      })
      this.temp = null
      this.temp = row
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    sortChange(data) {
      const { prop, order } = data
      if (order === 'ascending') {
        this.listQuery.sort = '+' + prop
      } else {
        this.listQuery.sort = '-' + prop
      }
      this.handleFilter()
    },
    handleSelect(key, keyPath) {
      this.listQuery.activeIndex = key
      this.handleFilter()
    },
    resetTemp() {
      this.temp = {
        name: '',
        identification: '',
        level: 1,
        state: 0,
        distribution_rule: [
          {
            name: '1级分销',
            level: 1,
            type: false,
            price: ''
          }
        ]
      }
    },
    handleCreate() {
      this.resetTemp()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    updateInput(e, index, name) { // 修改规则信息
      this.temp.distribution_rule[index][name] = e
      this.temp = JSON.parse(JSON.stringify(this.temp))
    },
    setLevel(e) { // 切换级别
      this.temp.distribution_rule = []
      for (let i = 0; i < e; i++) {
        this.temp.distribution_rule.push(
          {
            name: (i + 1) + '级分销',
            level: (i + 1),
            type: false,
            price: ''
          }
        )
      }
    },
    handleDelete(row) { // 删除
      const title = '删除后无法恢复，继续删除?'
      const win = '删除成功'
      this.$confirm(title, this.$t('hint.hint'), {
        confirmButtonText: this.$t('usuel.confirm'),
        cancelButtonText: this.$t('usuel.cancel'),
        type: 'warning'
      }).then(() => {
        this.formLoading = true
        destroy(row.id).then(() => {
          this.getList()
          this.dialogFormVisible = false
          this.formLoading = false
          this.$notify({
            title: this.$t('hint.succeed'),
            message: win,
            type: 'success',
            duration: 2000
          })
        }).catch(() => {
          this.formLoading = false
        })
      }).catch(() => {
      })
    },
    create() { // 添加
      this.formLoading = true
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          for (const item of this.temp.distribution_rule) {
            if (!item.name) {
              this.$message.error('别名不能为空')
              return false
            }
            if (!item.price) {
              this.$message.error('返佣值不能为空')
              return false
            }
          }
          create(this.temp).then(() => {
            this.getList()
            this.dialogFormVisible = false
            this.formLoading = false
            this.$notify({
              title: this.$t('hint.succeed'),
              message: this.$t('hint.creatingSuccessful'),
              type: 'success',
              duration: 2000
            })
          }).catch(() => {
            this.formLoading = false
          })
        } else {
          this.formLoading = false
        }
      })
    },
    edit() { // 更新
      this.formLoading = true
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          edit(this.temp).then(() => {
            this.getList()
            this.dialogFormVisible = false
            this.formLoading = false
            this.$notify({
              title: this.$t('hint.succeed'),
              message: this.$t('hint.updateSuccessful'),
              type: 'success',
              duration: 2000
            })
          }).catch(() => {
            this.formLoading = false
          })
        } else {
          this.formLoading = false
        }
      })
    }
  }
}
</script>
