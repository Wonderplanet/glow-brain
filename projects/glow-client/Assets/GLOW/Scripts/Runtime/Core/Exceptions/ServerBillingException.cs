using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public abstract class ServerBillingException : WrappedServerErrorException
    {
        public int ErrorCode { get; }

        protected ServerBillingException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
            ErrorCode = serverErrorException.ServerErrorCode;
        }
    }
}
