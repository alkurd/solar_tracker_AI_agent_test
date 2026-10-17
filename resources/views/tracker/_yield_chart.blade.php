<section class="yield-section" aria-labelledby="yield-title">
    <header class="yield-header">
        <div>
            <p class="section-kicker">Production history</p>
            <h2 class="yield-title" id="yield-title">Solar opbrengst</h2>
        </div>
        <div class="yield-tools">
            <div class="range-tools" role="group" aria-label="Grafiekperiode">
                <button class="range-tool" type="button" data-chart-range="1" aria-pressed="false">1D</button>
                <button class="range-tool" type="button" data-chart-range="7" aria-pressed="true">7D</button>
                <button class="range-tool" type="button" data-chart-range="14" aria-pressed="false">14D</button>
                <button class="range-tool" type="button" data-chart-range="all" aria-pressed="false">Alles</button>
            </div>
            <button class="chart-tool" type="button" data-chart-zoom="in" aria-label="Zoom in" title="Zoom in">+</button>
            <button class="chart-tool" type="button" data-chart-zoom="out" aria-label="Zoom uit" title="Zoom uit">-</button>
            <button class="chart-tool" type="button" data-chart-zoom="reset" aria-label="Reset grafiek" title="Reset grafiek">Reset</button>
        </div>
    </header>
    @if ($yieldData->isEmpty())
        <div class="chart-empty">Nog geen opbrengstmetingen ontvangen.</div>
    @else
        <div class="yield-chart-frame">
            <canvas id="yield-chart" data-points='@json($yieldData)' role="img" aria-label="Tijdgrafiek van de opbrengst van de zonnepanelen"></canvas>
        </div>
    @endif
</section>