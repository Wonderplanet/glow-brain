using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：許可レコード(allowance)が不正
    /// </summary>
    public class BillingInvalidAllowanceException : ServerBillingException
    {
        public BillingInvalidAllowanceException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
