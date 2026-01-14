using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyDuplicateUnitIdException : WrappedServerErrorException
    {
        public PartyDuplicateUnitIdException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
