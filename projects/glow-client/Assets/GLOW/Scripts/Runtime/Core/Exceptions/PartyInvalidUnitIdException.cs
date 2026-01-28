using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyInvalidUnitIdException : WrappedServerErrorException
    {
        public PartyInvalidUnitIdException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
