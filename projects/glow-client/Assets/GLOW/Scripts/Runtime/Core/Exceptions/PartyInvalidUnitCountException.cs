using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyInvalidUnitCountException : WrappedServerErrorException
    {
        public PartyInvalidUnitCountException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
