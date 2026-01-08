using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：購入キャンセルステータスのレシートだった(GooglePlayのみ)
    /// </summary>
    public class BillingGoogleplayReceiptStatusCanceledException : ServerBillingException
    {
        public BillingGoogleplayReceiptStatusCanceledException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
