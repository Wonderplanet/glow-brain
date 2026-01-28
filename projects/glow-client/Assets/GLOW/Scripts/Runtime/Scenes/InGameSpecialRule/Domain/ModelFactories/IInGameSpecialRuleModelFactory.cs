using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;

namespace GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories
{
    public interface IInGameSpecialRuleModelFactory
    {
        InGameSpecialRuleModel Create(InGameContentType contentType, MasterDataId targetMstId, QuestType questType);
    }
}
