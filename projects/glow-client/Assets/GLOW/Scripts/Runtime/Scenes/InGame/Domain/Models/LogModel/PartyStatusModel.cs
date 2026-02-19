using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.InGame.Domain.Models.LogModel
{
    public record PartyStatusModel(
        UserDataId UsrUnitId,
        MasterDataId MstUnitId,
        CharacterColor Color,
        CharacterUnitRoleType RoleType,
        HP Hp, //タワーとかパッシブバフを含めた数値
        AttackPower AttackPower, //パッシブバフを含めた数値
        UnitMoveSpeed UnitMoveSpeed,
        SummonCost SummonCost,
        TickCount SummonCoolTime,
        KnockBackCount KnockBackCount,
        MasterDataId MstAttackId,
        TickCount NormalAttackDelay,
        TickCount NormalAttackInterval,
        List<MasterDataId> MstUnitAbilityIds
    )
    {
        public static PartyStatusModel Empty { get; } = new(
            UserDataId.Empty,
            MasterDataId.Empty,
            CharacterColor.None,
            CharacterUnitRoleType.None,
            HP.Empty,
            AttackPower.Empty,
            UnitMoveSpeed.Empty,
            SummonCost.Empty,
            TickCount.Empty,
            KnockBackCount.Empty,
            MasterDataId.Empty,
            TickCount.Empty,
            TickCount.Empty,
            new List<MasterDataId>()
        );
    };
}
