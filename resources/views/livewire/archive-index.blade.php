<section class="archiveSec">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="archiveRow">
                    <div class="archivRght">
                        <div class="titleBx">فیلترها</div>
                        <div class="filterBox">
                            <form wire:submit.prevent="getData">
                                <div class="mb-3">
                                    <label for="sel01" class="form-label">بایگانی</label>
                                    <select wire:model="options.archive" class="form-select" id="sel01">
                                        <option selected value="all">همه</option>
                                        @foreach($list as $key => $name)
                                            <option value="{{ $key }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="inpt02" class="form-label">انتخاب شماره</label>
                                    <input type="text" wire:model="options.number" class="form-control" id="inpt02"/>
                                </div>
                                <div class="mb-3">
                                    <label for="datepicker1" class="form-label">از تاریخ</label>
                                    <input type="text" wire:model="options.from_date" class="form-control jalali-datepicker" id="datepicker1"/>
                                </div>

                                <div class="mb-3">
                                    <label for="datepicker2" class="form-label">تا تاریخ</label>
                                    <input type="text" wire:model="options.to_date" class="form-control jalali-datepicker" id="datepicker2"/>
                                </div>

                                <button type="submit" class="btn filterBtn transitionCls">
                                    اعمال فیلتر
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="archivLeft">
                        <div class="archivSort">
                            <div class="form-check position-relative">
                                <input class="form-check-input position-absolute" type="radio" name="all"
                                       id="all" value="all" wire:model="options.archive" wire:change="getData"/>
                                <label class="form-check-label transitionCls position-relative" for="all">
                                    همه
                                </label>
                            </div>

                            @foreach($list as $key => $name)
                                <div class="form-check position-relative">
                                    <input class="form-check-input position-absolute" type="radio"
                                           name="archive_filter" id="{{ $key }}" value="{{ $key }}"
                                           wire:model="options.archive" wire:change="getData"/>
                                    <label class="form-check-label transitionCls position-relative" for="{{ $key }}">
                                        {{ $name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="archiveList">
                            @foreach($archives as $item)
                                <a href="{{ $item->getUrl() }}" class="archiveCard transitionCls">
                                    <div class="imgBox">
                                        <img src="{{ $item->getImageUrl('medium') }}" alt="img"/>
                                    </div>
                                    <div class="text">
                                        <p>{{ $item->archive }}</p>
                                        <div>
                                            <span>شماره {{ $item->archive_number }}</span>
                                            <span>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($item->archive_date))->format('Y/m/d') }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                            {{ $archives->links('vendor.pagination.custom') }}
                    </div>
                </div>

                <script>
                    document.addEventListener('livewire:load', function () {
                        // Initialize persian-datepicker for both inputs
                        $('.jalali-datepicker').pDatepicker({
                            format: 'YYYY-MM-DD',
                            calendar: 'persian',
                            toolbox: {
                                calendarSwitch: {
                                    enabled: false // Disables switching to Gregorian
                                }
                            },
                            onSelect: function (unix) {
                                // Convert selected date to YYYY-MM-DD format
                                let date = new persianDate(unix).format('YYYY-MM-DD');
                                // Trigger Livewire update
                                let input = this.$element[0];
                            @this.set(input.getAttribute('wire:model'), date)
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</section>
