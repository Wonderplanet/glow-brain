using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class GimmickObjectRemovingProcess : IGimmickObjectRemovingProcess
    {
        [Inject] IMarchingLaneDirector MarchingLaneDirector { get; }

        public GimmickObjectRemovingProcessResult Update(
            IReadOnlyList<InGameGimmickObjectModel> gimmickObjects)
        {
            var removeObjects = gimmickObjects
                .Where(gimmick => gimmick.IsNeedsRemoval)
                .ToList();

            // 残るギミックオブジェクト
            var updatedGimmickObjects = gimmickObjects.Except(removeObjects).ToList();

            return new GimmickObjectRemovingProcessResult(
                updatedGimmickObjects,
                removeObjects);
        }
    }
}
