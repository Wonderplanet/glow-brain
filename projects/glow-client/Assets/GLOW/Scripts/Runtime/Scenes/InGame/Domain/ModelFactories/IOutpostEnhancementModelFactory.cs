using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public interface IOutpostEnhancementModelFactory
    {
        OutpostEnhancementModel Create();
        OutpostEnhancementModel CreateForTutorialBattle();
        OutpostEnhancementModel CreateOpponent(
            IReadOnlyList<UserOutpostEnhanceModel> userOutpostEnhanceList);
    }
}

