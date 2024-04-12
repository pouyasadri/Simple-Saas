export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    available_credits: number;
}

export interface FeatureType {
    id: number;
    image: string;
    name: string;
    route_name: string;
    is_active: boolean;
    description: string;
    required_credits: number;
}

export interface PackageType {
    id: number;
    name: string;
    price: number;
    credits: number;
}

export interface Features {
    data: FeatureType[];
}

export interface Packages {
    data: PackageType[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};
