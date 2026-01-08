using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ExchangeLineupMismatch : WrappedServerErrorException
    {
        public ExchangeLineupMismatch(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
