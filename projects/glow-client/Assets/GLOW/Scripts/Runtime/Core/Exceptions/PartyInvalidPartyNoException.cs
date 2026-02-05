using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyInvalidPartyNoException : WrappedServerErrorException
    {
        public PartyInvalidPartyNoException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
