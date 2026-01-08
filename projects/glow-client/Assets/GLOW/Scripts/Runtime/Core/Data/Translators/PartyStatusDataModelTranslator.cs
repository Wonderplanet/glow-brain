using GLOW.Core.Data.Data;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Data.Translators
{
    public class PartyStatusDataModelTranslator
    {
        public static PartyStatusData ToPartyStatusData(PartyStatusModel partyStatusModel)
        {
            var mstUnitAbilityIds = partyStatusModel.MstUnitAbilityIds;
            
            return new PartyStatusData()
            {
                UsrUnitId = partyStatusModel.UsrUnitId.Value,
                MstUnitId = partyStatusModel.MstUnitId.Value,
                Color = partyStatusModel.Color.ToString(),
                RoleType = partyStatusModel.RoleType.ToString(),
                Hp = partyStatusModel.Hp.Value,
                Atk = (int)partyStatusModel.AttackPower.Value,
                MoveSpeed = partyStatusModel.UnitMoveSpeed.Value,
                SummonCost = partyStatusModel.SummonCost.Value,
                SummonCoolTime = (int)partyStatusModel.SummonCoolTime.Value,
                DamageKnockBackCount = partyStatusModel.KnockBackCount.Value,
                SpecialAttackMstAttackId = partyStatusModel.MstAttackId.Value,
                AttackDelay = (int)partyStatusModel.NormalAttackDelay.Value,
                NextAttackInterval = (int)partyStatusModel.NormalAttackInterval.Value,
                MstUnitAbility1 = mstUnitAbilityIds.Count > 0 ? mstUnitAbilityIds[0].Value : string.Empty,
                MstUnitAbility2 = mstUnitAbilityIds.Count > 1 ? mstUnitAbilityIds[1].Value : string.Empty,
                MstUnitAbility3 = mstUnitAbilityIds.Count > 2 ? mstUnitAbilityIds[2].Value : string.Empty,
            };
        }
    }
}
