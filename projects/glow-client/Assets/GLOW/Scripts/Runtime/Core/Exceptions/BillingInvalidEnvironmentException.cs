using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：使用できない環境
    /// </summary>
    public class BillingInvalidEnvironmentException : ServerBillingException
    {
        public BillingInvalidEnvironmentException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
