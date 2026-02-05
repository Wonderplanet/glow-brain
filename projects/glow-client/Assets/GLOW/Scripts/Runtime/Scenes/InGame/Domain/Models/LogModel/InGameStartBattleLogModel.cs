using System.Collections.Generic;

namespace GLOW.Scenes.InGame.Domain.Models.LogModel
{
    public record InGameStartBattleLogModel(IReadOnlyList<PartyStatusModel> PartyStatusModels);
}
