using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：購入ペンディングステータスのレシートだった(GooglePlayのみ)
    /// </summary>
    public class BillingGoogleplayReceiptStatusPendingException : ServerBillingException
    {
        public BillingGoogleplayReceiptStatusPendingException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
