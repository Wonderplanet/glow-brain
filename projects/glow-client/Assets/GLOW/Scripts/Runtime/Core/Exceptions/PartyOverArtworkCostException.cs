using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyOverArtworkCostException : WrappedServerErrorException
    {
        public PartyOverArtworkCostException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}

