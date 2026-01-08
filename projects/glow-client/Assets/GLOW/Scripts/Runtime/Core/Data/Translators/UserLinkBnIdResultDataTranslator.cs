using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserLinkBnIdResultDataTranslator
    {
        public static UserLinkBnIdResultModel Translate(UserLinkBnidResultData data)
        {
            return new UserLinkBnIdResultModel(
                string.IsNullOrEmpty(data.IdToken) ? BnIdToken.Empty : new BnIdToken(data.IdToken),
                data.BnidLinkedAt);
        }
    }
}
