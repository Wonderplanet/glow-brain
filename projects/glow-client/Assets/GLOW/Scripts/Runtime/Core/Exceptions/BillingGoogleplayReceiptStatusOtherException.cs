using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：その他、正常ではないステータスだった(GooglePlayのみ)
    /// </summary>
    public class BillingGoogleplayReceiptStatusOtherException : ServerBillingException
    {
        public BillingGoogleplayReceiptStatusOtherException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
