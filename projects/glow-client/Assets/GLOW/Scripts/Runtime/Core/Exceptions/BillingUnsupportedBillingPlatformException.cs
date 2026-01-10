using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：未対応の課金プラットフォーム
    /// </summary>
    public class BillingUnsupportedBillingPlatformException : ServerBillingException
    {
        public BillingUnsupportedBillingPlatformException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
