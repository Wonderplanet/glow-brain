using System;

namespace WPFramework.Exceptions
{
    public class AssetBundleContentCatalogUpdateFailedException : Exception
    {
        public AssetBundleContentCatalogUpdateFailedException()
        {
        }

        public AssetBundleContentCatalogUpdateFailedException(string message) : base(message)
        {
        }
    }
}
