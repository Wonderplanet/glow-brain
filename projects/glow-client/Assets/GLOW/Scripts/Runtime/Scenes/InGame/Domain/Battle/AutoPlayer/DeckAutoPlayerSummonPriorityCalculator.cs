using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class DeckAutoPlayerSummonPriorityCalculator : IDeckAutoPlayerSummonPriorityCalculator
    {
        // ロールごとの優先度（指定がないロールは優先度「1」）
        static readonly IReadOnlyDictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority> RolePriorityDictionary = new Dictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority>
        {
            {CharacterUnitRoleType.Defense, new DeckAutoPlayerSummonPriority(3)},
            {CharacterUnitRoleType.Attack, new DeckAutoPlayerSummonPriority(2)},
        };
        
        static readonly DeckAutoPlayerSummonPriority RolePriorityOffset = new DeckAutoPlayerSummonPriority(10);
        static readonly DeckAutoPlayerSummonPriority CoolTimePriorityOffset = new DeckAutoPlayerSummonPriority(2);

        BattlePoint _maxSummonCost = BattlePoint.Empty;
        DeckAutoPlayerSummonPriority _summonCostPriorityOffset = DeckAutoPlayerSummonPriority.One;
        
        public void Initialize(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            _maxSummonCost = deckUnits
                .Select(deckUnit => deckUnit.SummonCost)
                .MaxBy(cost => cost.Value) ?? BattlePoint.Empty;
            
            _summonCostPriorityOffset = _maxSummonCost.ToDeckAutoPlayerSummonPriority() + 1;
        }
        
        public DeckAutoPlayerSummonPriority CalculatePriority(
            DeckUnitModel deckUnit, 
            int summoningCount,
            Dictionary<CharacterUnitRoleType, int> summoningCountDictionary)
        {
            var priority = GetSummoningCountPriority(deckUnit.RoleType, summoningCountDictionary);
            
            priority *= RolePriorityOffset;
            priority += GetRolePriority(deckUnit.RoleType);
            
            priority *= CoolTimePriorityOffset;
            priority += GetCoolTimePriority(deckUnit);
            
            priority *= _summonCostPriorityOffset;
            priority += GetSummonCostPriority(deckUnit.SummonCost, summoningCount);
            
            return priority; 
        }
        
        DeckAutoPlayerSummonPriority GetSummoningCountPriority(
            CharacterUnitRoleType roleType, 
            Dictionary<CharacterUnitRoleType, int> summoningCountDictionary)
        {
            const int maxSummoningCount = 10;
            
            // 同じロールのキャラが多く召喚されているほど優先度を低くする
            return summoningCountDictionary.TryGetValue(roleType, out var count) 
                ? new DeckAutoPlayerSummonPriority(maxSummoningCount - count) 
                : new DeckAutoPlayerSummonPriority(maxSummoningCount);
        }
        
        DeckAutoPlayerSummonPriority GetRolePriority(CharacterUnitRoleType roleType)
        {
            return RolePriorityDictionary.TryGetValue(roleType, out var value) 
                ? value 
                : DeckAutoPlayerSummonPriority.One;
        }
        
        DeckAutoPlayerSummonPriority GetCoolTimePriority(DeckUnitModel deckUnit)
        {
            // クールタイム中のキャラは優先度を下げる
            return deckUnit.RemainingSummonCoolTime.IsZero() 
                ? DeckAutoPlayerSummonPriority.Two 
                : DeckAutoPlayerSummonPriority.One;
        }
        
        DeckAutoPlayerSummonPriority GetSummonCostPriority(BattlePoint summonCost, int summoningCount)
        {
            // 5体以上召喚中のときは召喚コストが高いキャラを優先
            if (summoningCount >= 5)
            {
                return summonCost.ToDeckAutoPlayerSummonPriority();
            }
            
            // 召喚中キャラが5体未満のときは召喚コストが低いキャラを優先
            return (_maxSummonCost - summonCost).ToDeckAutoPlayerSummonPriority();
        }
    }
}