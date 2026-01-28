using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record GimmickObjectRemovingProcessResult(
        IReadOnlyList<InGameGimmickObjectModel> UpdatedGimmickObjects,
        IReadOnlyList<InGameGimmickObjectModel> RemovedGimmickObjects)
    {
        public static GimmickObjectRemovingProcessResult Empty { get; } = new (
            new List<InGameGimmickObjectModel>(),
            new List<InGameGimmickObjectModel>());
    }
}
