using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：AppStoreのバンドルIDが一致しない(AppStoreのみ)
    /// </summary>
    public class BillingAppstoreBundleIdNotMatchException : ServerBillingException
    {
        public BillingAppstoreBundleIdNotMatchException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
