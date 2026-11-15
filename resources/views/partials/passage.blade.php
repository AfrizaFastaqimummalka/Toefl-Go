<details class="passage" open x-show="q.passage" x-cloak>
    <summary><span x-text="q.passage ? q.passage.title : ''"></span> <span class="small muted">(tap untuk buka/tutup)</span></summary>
    <ol class="passage-lines">
        <template x-for="(l, n) in (q.passage ? q.passage.lines : [])" :key="n">
            <li><span class="ln" x-text="(n === 0 || (n + 1) % 5 === 0) ? n + 1 : ''"></span><span x-text="l"></span></li>
        </template>
    </ol>
</details>
