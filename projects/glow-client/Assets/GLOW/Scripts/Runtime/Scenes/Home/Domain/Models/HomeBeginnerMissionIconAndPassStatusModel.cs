using System.Collections.Generic;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeBeginnerMissionIconAndPassStatusModel(
        BeginnerMissionFinishedFlag BeginnerMissionFinishedFlag,
        IReadOnlyList<HeldPassEffectDisplayModel> HeldPassEffectDisplayModels);
}
