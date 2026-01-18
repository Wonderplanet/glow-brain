using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public class UserGachaUpperDataTranslator
    {
        public static UserDrawCountThresholdModel ToUserDrawCountThresholdModel(UsrGachaUpperData data)
        {
            return new UserDrawCountThresholdModel(
                new DrawCountThresholdGroupId(data.UpperGroup),
                data.UpperType,
                new GachaPlayedCount(data.Count));
        }
    }
}
