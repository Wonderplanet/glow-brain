using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    /// <summary>
    /// キャラをランダムにレーンに割り当てる
    /// </summary>
    public class RandomMarchingLaneGroup : IMarchingLaneGroup
    {
        readonly MarchingLaneGroupId _groupId;
        readonly MarchingLaneGroupType _groupType;

        readonly int _numberOfLanes;
        readonly List<FieldObjectId>[] _lanes;
        readonly List<int> _emptyLaneIndexList = new ();
        readonly IRandomProvider _randomProvider;

        public MarchingLaneGroupId GroupId => _groupId;
        public MarchingLaneGroupType GroupType => _groupType;
        public int NumberOfLanes => _numberOfLanes;
        public MarchingLaneIndex LaneIndexOffset { get; set; }

        public RandomMarchingLaneGroup(
            MarchingLaneGroupId groupId,
            MarchingLaneGroupType groupType,
            int numberOfLanes,
            IRandomProvider randomProvider)
        {
            _groupId = groupId;
            _groupType = groupType;

            _numberOfLanes = numberOfLanes;
            _randomProvider = randomProvider;

            _lanes = new List<FieldObjectId>[numberOfLanes];
            for (int i = 0; i < numberOfLanes; i++)
            {
                _lanes[i] = new List<FieldObjectId>();
            }

            // 空きレーンのインデックスを保持するリストに、レーン数分のインデックスをランダムに入れる
            var indexList = Enumerable.Range(0, numberOfLanes).ToList();
            for (int i = 0; i < numberOfLanes; i++)
            {
                var index = _randomProvider.Range(0, indexList.Count);
                _emptyLaneIndexList.Add(indexList[index]);
                indexList.RemoveAt(index);
            }
        }

        public MarchingLaneIdentifier AssignLane(FieldObjectId fieldObjectId, MarchingLaneIdentifier specifiedLane)
        {
            var laneIndex = TakeEmptyLaneIndex(specifiedLane);

            _lanes[laneIndex].Add(fieldObjectId);

            var laneIdentifier = new MarchingLaneIdentifier(
                _groupId,
                new MarchingLaneIndex(laneIndex),
                LaneIndexOffset);

            return laneIdentifier;
        }

        public void Withdraw(FieldObjectId fieldObjectId, MarchingLaneIdentifier laneIdentifier)
        {
            if (laneIdentifier.IsEmpty()) return;
            if (laneIdentifier.LaneGroupId != _groupId) return;
            if (laneIdentifier.LaneIndex.Value >= _lanes.Length) return;

            var laneIndex = laneIdentifier.LaneIndex.Value;
            _lanes[laneIndex].Remove(fieldObjectId);

            if (_lanes[laneIndex].Count == 0)
            {
                _emptyLaneIndexList.Add(laneIndex);
            }
        }

        int TakeEmptyLaneIndex(MarchingLaneIdentifier specifiedLane)
        {
            if (!specifiedLane.IsEmpty())
            {
                _emptyLaneIndexList.Remove(specifiedLane.LaneIndex.Value);
                return specifiedLane.LaneIndex.Value;
            }

            if (_emptyLaneIndexList.Count > 0)
            {
                int index = _emptyLaneIndexList.First();
                _emptyLaneIndexList.RemoveAt(0);
                return index;
            }

            return _randomProvider.Range(0, _numberOfLanes);
        }
    }
}
