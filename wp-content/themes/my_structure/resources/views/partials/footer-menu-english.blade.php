@php
    $options = \Models\Options\OpzioniGlobaliFields::get();
@endphp
<footer class="bg-white">
    <div class="mx-auto space-y-4 px-0 pt-8 sm:px-0 lg:px-0">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 px-4 sm:px-6 lg:px-8">
            <div>
                <div class="text-teal-600">
                    <div class="text-center flex flex-col items-center justify-between">
                        <a href="#" title="" class="flex">
                            <img class="w-auto h-12 lg:h-12" src="{{$options->logo['url']}}" alt="" />
                        </a>
                        <div class="text-custom-dark-green font-bold text-xs">Project Africa Conservation</div>
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <p class="mt-4 max-w-xs text-gray-500 text-center">
                        The difference is you: every small gesture creates a big change.
                    </p>
                </div>

                <ul class="mt-6 flex justify-center gap-6">
                    <li>
                        <a href="https://www.facebook.com/share/15kZKmU4gr/" rel="noreferrer" target="_blank" class="text-gray-700 transition hover:opacity-75">
                            <span class="sr-only">Facebook</span>
                            <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </li>

                    <li>
                        <a href="https://www.instagram.com/pacitalia?igsh=MWkycW1lZnRmNnAxMA==" rel="noreferrer"  target="_blank" class="text-gray-700 transition hover:opacity-75">
                            <span class="sr-only">Instagram</span>
                            <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.linkedin.com/in/project-africa-conservation-a-p-s-b81a95340/" rel="noreferrer" target="_blank" class="text-gray-700 transition hover:opacity-75">
                            <span class="sr-only">Linkedin</span>
                            <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin size-6" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-1 lg:col-span-2 lg:grid-cols-1">
                <div>
                    <ul class="mt-6 sm:mt-3 flex flex-wrap justify-center gap-6 md:gap-8 lg:gap-12">


                        @foreach($menu as $key => $item)
                            <li>
                                <a class="text-gray-700 transition hover:text-gray-700/75" href="{{$item->url}}"> {{$item->title}} </a>
                            </li>
                            @if(!empty($item->children))
                                @foreach($item->children as $subkey => $subitem)
                                    <li>
                                        <a class="text-gray-700 transition hover:text-gray-700/75" href="{{$subitem->url}}"> {{$subitem->title}} </a>
                                    </li>
                                @endforeach
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div>
                    <ul class="mt-6 sm:mt-3 flex flex-wrap justify-center gap-6 md:gap-8 lg:gap-12 text-sm">
                        <li>
                            <a
                                    class="flex items-center justify-center gap-1.5 ltr:sm:justify-start rtl:sm:justify-end text-gray-700 transition hover:text-gray-700/75"
                                    href="#"
                            >
                                <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5 shrink-0 text-gray-900"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                >
                                    <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>

                                <span class="flex-1 text-gray-700">info@project-africa-conservation.org</span>
                            </a>
                        </li>

                        <li class="flex items-start justify-center gap-1.5 ltr:sm:justify-start rtl:sm:justify-end">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-gray-900" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>

                            <address class="-mt-0.5 flex-1 not-italic text-gray-700">
                                Via Cavour 7 -12042, Bra (CN)
                            </address>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        </div>
        <div class="flex justify-center">
            <div class="px-4">
                <p class="mt-4 max-w-xs text-center text-xs text-gray-500"> &copy; 2024. PAC - Project Africa Conservation A.P.S. All rights reserved.
                    <br> &copy; This site is protected by reCAPTCHA and the Google Privacy Policy and Terms of Service apply. </p>
            </div>
        </div>
        <div class="flex justify-center bg-[#45752c]">
            <div>
                <p class="text-xs text-white">Created with &hearts; by Marian</p>
            </div>
        </div>
    </div>
</footer>