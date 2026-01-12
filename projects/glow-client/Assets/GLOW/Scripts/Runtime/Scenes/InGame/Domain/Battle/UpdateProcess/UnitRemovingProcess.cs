using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class UnitRemovingProcess : IUnitRemovingProcess
    {
        [Inject] IMarchingLaneDirector MarchingLaneDirector { get; }

        public UnitRemovingProcessResult Update(
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<SpecialUnitModel> specialUnits)
        {
            var deadUnits = new List<CharacterUnitModel>();
            var vanishedUnits = new List<CharacterUnitModel>();
            var updatedUnits = new List<CharacterUnitModel>();
            var defeatUnitsDictionary = new Dictionary<MasterDataId, DefeatEnemyCount>();
            var defeatEnemyCount = 0;
            var defeatBossEnemyCount = 0;

            for (var i = 0; i < units.Count; i++)
            {
                var unit = units[i];
                
                if (unit.IsDead)
                {
                    deadUnits.Add(unit);
                    
                    if (unit.BattleSide == BattleSide.Enemy)
                    {
                        defeatEnemyCount++;
                        
                        if (unit.IsBoss)
                        {
                            defeatBossEnemyCount++;
                        }
                        
                        // Dictionary更新
                        if (defeatUnitsDictionary.TryGetValue(unit.CharacterId, out var existingCount))
                        {
                            defeatUnitsDictionary[unit.CharacterId] = existingCount + 1;
                        }
                        else
                        {
                            defeatUnitsDictionary[unit.CharacterId] = DefeatEnemyCount.One;
                        }
                    }
                }
                else if (unit.IsVanished)
                {
                    vanishedUnits.Add(unit);
                }
                else
                {
                    updatedUnits.Add(unit);
                }
            }

            // 除去するキャラの全体
            var removedUnits = new List<CharacterUnitModel>(deadUnits.Count + vanishedUnits.Count);
            removedUnits.AddRange(deadUnits);
            removedUnits.AddRange(vanishedUnits);

            // 侵攻レーンから除く
            for (var i = 0; i < removedUnits.Count; i++)
            {
                var unit = removedUnits[i];
                MarchingLaneDirector.WithdrawFromLane(unit.Id, unit.MarchingLane);
            }

            // ロールがスペシャルのキャラの除外処理
            var removedSpecialUnits = new List<SpecialUnitModel>();
            var updatedSpecialUnits = new List<SpecialUnitModel>();
            
            for (var i = 0; i < specialUnits.Count; i++)
            {
                var unit = specialUnits[i];
                if (unit.RemainingLeavingTime.IsEmpty())
                {
                    removedSpecialUnits.Add(unit);
                }
                else
                {
                    updatedSpecialUnits.Add(unit);
                }
            }

            return new UnitRemovingProcessResult(
                removedUnits,
                deadUnits,
                updatedUnits,
                removedSpecialUnits,
                updatedSpecialUnits,
                new DefeatEnemyCount(defeatEnemyCount),
                new DefeatBossEnemyCount(defeatBossEnemyCount),
                defeatUnitsDictionary);
        }
    }
}
