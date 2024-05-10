
    <div x-data="{
                            state: @this.entangle('{{ $getStatePath() }}'),
                            label() {
                                return this.state ? this.state.label : ''
                            },

                            async init() {
                                let searchLabel = '{{ __('Search a location') }}';

                                if (this.state) {
                                    searchLabel = this.state.label;
                                }

                                const popup = L.popup();

                                const defPos = [8.9475,125.5406]

                                const map = L.map('{{ $getId() }}', {
                                    zoomControl: {{ $getZoomControl() }},
                                    scrollWheelZoom: {{ $getScrollWheelZoom() }}
                                }).setView(defPos, 13);

                                L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                                let defaultMarker = null;

                                if (this.state) {
                                    defaultMarker = L.marker([this.state.y, this.state.x]).addTo(map);
                                    map.setView([this.state.y, this.state.x], {{ $getZoomLevel() }});
                                }

                                const that = this;

                                const provider = new GeoSearch.OpenStreetMapProvider();

                                if(!this.state) {
                                    const obj = this.state;
                                    const defaultResult = await provider.search({query:this.state ? [obj.y, obj.x] : defPos.join(', ')})

                                    if(Array.isArray(defaultResult) && defaultResult.length > 0) {
                                        that.state = defaultResult[0];
                                    }
                                }

                                const Imarker = new L.marker(this.state ? [this.state.y, this.state.x] : defPos, {draggable:'true'});

                                Imarker.on('dragend', async function(event){
                                    let marker = event.target;
                                    let position = marker.getLatLng();
                                    marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
                                    map.panTo(new L.LatLng(position.lat, position.lng))

                                    const result = await provider.search({query: `${position.lat}, ${position.lng}`})

                                    if(Array.isArray(result) && result.length > 0) {
                                        that.state = result[0];

                                        popup.setContent(that.state.label);

                                        Imarker.bindPopup(popup).openPopup();
                                    }
                                  });

                                  map.addLayer(Imarker);


                                const search = new GeoSearch.GeoSearchControl({
                                    provider: provider,
                                    style: 'bar',
                                    showMarker: false,
                                    maxMarker: 1,
                                    autoClose: true,
                                    autoComplete: true,
                                    retainZoomLevel: false,
                                    maxSuggestions: 5,
                                    keepResult: true,
                                    searchLabel: searchLabel,
                                    resultFormat: function(t) {
                                        return '' + t.result.label;
                                    },
                                    marker: {
                                        draggable: true,
                                    },
                                    updateMap: !0
                                });

                                map.addControl(search);

                                map.on('geosearch/showlocation', function(location) {
                                    that.state = location.location;
                                    Imarker.setLatLng(new L.LatLng(that.state.y, that.state.x),{draggable:'true'})
                                    popup.setContent(that.state.label);

                                    Imarker.bindPopup(popup).openPopup();
                                });
                            }
                        }">

        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="data.name">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">

                Location<!--[if BLOCK]><![endif]--><sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                <!--[if ENDBLOCK]><![endif]-->
            </span>
        </label>
        <div id="{{ $getId() }}" style="height: {{$getMapHeight()}}px; z-index: 0;" class="w-full rounded-lg shadow-sm mt-1" wire:ignore></div>
        <div x-text="label()" class="font-medium text-xs mt-2">Loading location name...</div>
        @push('scripts')
            @if($isViewRecord())
                <style>
                    .leaflet-control-geosearch {
                        display: none;
                    }
                </style>
            @endif
        @endpush
    </div>
