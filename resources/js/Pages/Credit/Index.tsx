import {Features, Packages, PageProps} from "@/types";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import {Head} from "@inertiajs/react";
import PackagesPricingCard from "@/Components/PackagesPricingCard";

export default function Index({auth, packages, features, success, error}: {
    auth: PageProps['auth'],
    packages: Packages,
    features: Features,
    success: string | null,
    error: string | null
}) {
    const available_credits = auth.user.available_credits;
    return (
        <Authenticated user={auth.user}
                       header={<h2
                           className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Your
                           Credits</h2>}>
            <Head title="Your Credits"/>
            <div className={"py-12"}>
                <div className={"max-w-7xl mx-auto sm:px-6 lg:px-8"}>
                    {success !== null && (
                        <div className={"mb-4 p-3 rounded-lg text-gray-100 bg-emerald-500 "}>
                            {success}
                        </div>
                    )}
                    {error !== null && (
                        <div className={"mb-4 p-3 rounded-lg text-gray-100 bg-red-500 "}>
                            {error}
                        </div>
                    )}
                    <div className={"bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative"}>
                        <div className={"flex flex-col gap-3 items-center p-4"}>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5"
                                 stroke="currentColor" className="w-[100px] text-white">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                      d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
                            </svg>
                            <h3 className={"text-white text-2xl"}>
                                You have {available_credits} credits
                            </h3>
                        </div>
                    </div>
                    <PackagesPricingCard packages={packages.data} features={features.data}/>
                </div>
            </div>
        </Authenticated>
    );
}
