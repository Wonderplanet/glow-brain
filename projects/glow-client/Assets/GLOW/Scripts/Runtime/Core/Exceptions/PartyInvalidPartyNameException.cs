using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyInvalidPartyNameException : WrappedServerErrorException
    {
        public PartyInvalidPartyNameException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
