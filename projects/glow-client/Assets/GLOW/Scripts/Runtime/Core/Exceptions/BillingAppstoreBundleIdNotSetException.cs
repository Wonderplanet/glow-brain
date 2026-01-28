using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：設定からbunndle_idが取得できない(AppStoreのみ)
    /// </summary>
    public class BillingAppstoreBundleIdNotSetException : ServerBillingException
    {
        public BillingAppstoreBundleIdNotSetException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
