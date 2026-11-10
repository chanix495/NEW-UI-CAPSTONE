<select class="inp" style="width:auto;padding:8px 14px;border-radius:10px" x-model="period" @change="changePeriod()">
    <template x-for="p in periods" :key="p.value"><option :value="p.value" x-text="p.label" :selected="p.value === period"></option></template>
</select>
