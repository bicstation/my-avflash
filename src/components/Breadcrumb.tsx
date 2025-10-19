// src/components/Breadcrumb.tsx
import Link from 'next/link';

interface BreadcrumbItem {
  label: string;
  href: string;
}

interface BreadcrumbProps {
  items: BreadcrumbItem[];
}

export default function Breadcrumb({ items }: BreadcrumbProps) {
  return (
    <nav className="bg-gray-200 p-3 rounded-md text-sm text-gray-600 mb-6 shadow-sm" aria-label="breadcrumb">
      <ol className="list-none p-0 inline-flex space-x-2">
        {items.map((item, index) => (
          <li key={index} className="flex items-center">
            {index > 0 && <span className="mx-2 text-gray-400">/</span>}
            {index === items.length - 1 ? (
              <span className="font-medium text-gray-800">{item.label}</span>
            ) : (
              <Link href={item.href} className="hover:text-blue-600 transition">
                {item.label}
              </Link>
            )}
          </li>
        ))}
      </ol>
    </nav>
  );
}