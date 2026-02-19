using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public interface IMarchingLaneGroup
    {
        MarchingLaneGroupId GroupId { get; }
        MarchingLaneGroupType GroupType { get; }
        int NumberOfLanes { get; }
        MarchingLaneIndex LaneIndexOffset { get; set; }

        MarchingLaneIdentifier AssignLane(FieldObjectId fieldObjectId, MarchingLaneIdentifier specifiedLane);
        void Withdraw(FieldObjectId fieldObjectId, MarchingLaneIdentifier laneIdentifier);
    }
}
