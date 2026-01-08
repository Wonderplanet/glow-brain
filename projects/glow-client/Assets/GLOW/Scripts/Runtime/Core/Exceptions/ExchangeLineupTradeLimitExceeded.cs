using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ExchangeLineupTradeLimitExceeded : WrappedServerErrorException
    {
        public ExchangeLineupTradeLimitExceeded(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
