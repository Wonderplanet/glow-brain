using Cysharp.Text;
using GLOW.Core.Constants;

namespace GLOW.Core.Modules.BnIdLinker
{
    public sealed class BnIdLinker
    {
        public string GetBnIdLink()
        {
            var url = Credentials.BnIdLinkURL;
#if DEBUG
            url = Credentials.CPBnIdLinkURL;
#endif // DEBUG

            return url;
        }
    }
}
