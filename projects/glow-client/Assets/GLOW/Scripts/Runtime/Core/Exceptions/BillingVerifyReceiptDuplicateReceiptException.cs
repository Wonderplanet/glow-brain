using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：処理済みの重複レシート
    /// </summary>
    public class BillingVerifyReceiptDuplicateReceiptException : ServerBillingException
    {
        public BillingVerifyReceiptDuplicateReceiptException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
