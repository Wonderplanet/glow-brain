using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public interface IMarchingLaneDirector
    {
        void Initialize();
        
        MarchingLaneIdentifier AssignLane(
            FieldObjectId fieldObjectId,
            BattleSide battleSide,
            bool isBoss,
            MarchingLaneIdentifier specifiedLane);

        void WithdrawFromLane(FieldObjectId fieldObjectId, MarchingLaneIdentifier laneIdentifier);
    }
}
