using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.MarchingLane
{
    public class MarchingLaneDirector : IMarchingLaneDirector
    {
        const int PlayerBaseLaneCount = 99;
        const int EnemyBaseLaneCount = 95;
        const int BossBaseLaneCount = 4;

        [Inject] IRandomProvider RandomProvider { get; }

        List<IMarchingLaneGroup> _playerLaneGroups;
        List<IMarchingLaneGroup> _enemyLaneGroups;

        public void Initialize()
        {
            _playerLaneGroups = new List<IMarchingLaneGroup>
            {
                new RandomMarchingLaneGroup(
                    new MarchingLaneGroupId("Player"),
                    MarchingLaneGroupType.Base,
                    PlayerBaseLaneCount,
                    RandomProvider),
            };

            _enemyLaneGroups = new List<IMarchingLaneGroup>
            {
                new RandomMarchingLaneGroup(
                    new MarchingLaneGroupId("Enemy"),
                    MarchingLaneGroupType.Base,
                    EnemyBaseLaneCount,
                    RandomProvider),
                new RandomMarchingLaneGroup(
                    new MarchingLaneGroupId("Boss"),
                    MarchingLaneGroupType.Boss,
                    BossBaseLaneCount,
                    RandomProvider),
            };

            SetupLaneGroups(_playerLaneGroups);
            SetupLaneGroups(_enemyLaneGroups);
        }

        MarchingLaneIdentifier IMarchingLaneDirector.AssignLane(
            FieldObjectId fieldObjectId,
            BattleSide battleSide,
            bool isBoss,
            MarchingLaneIdentifier specifiedLane)
        {
            var laneGroupType = isBoss ? MarchingLaneGroupType.Boss : MarchingLaneGroupType.Base;

            var laneIdentifier = GetLaneGroups(battleSide)
                .First(laneGroup => laneGroup.GroupType == laneGroupType)
                .AssignLane(fieldObjectId, specifiedLane);

            return laneIdentifier;
        }

        public void WithdrawFromLane(FieldObjectId fieldObjectId, MarchingLaneIdentifier laneIdentifier)
        {
            var laneGroup = _playerLaneGroups.Concat(_enemyLaneGroups)
                .FirstOrDefault(laneGroup => laneGroup.GroupId == laneIdentifier.LaneGroupId);

            if (laneGroup != null)
            {
                laneGroup.Withdraw(fieldObjectId, laneIdentifier);
            }
        }

        void SetupLaneGroups(List<IMarchingLaneGroup> laneGroups)
        {
            var laneIndexOffset = MarchingLaneIndex.Zero;

            foreach (var laneGroup in laneGroups)
            {
                laneGroup.LaneIndexOffset = laneIndexOffset;
                laneIndexOffset += laneGroup.NumberOfLanes;
            }
        }

        List<IMarchingLaneGroup> GetLaneGroups(BattleSide battleSide)
        {
            return battleSide == BattleSide.Player ? _playerLaneGroups : _enemyLaneGroups;
        }
    }
}
