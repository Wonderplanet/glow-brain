using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IAttackResultModelFactory
    {
        HitAttackResultModel CreateHitAttackResult(IAttackModel attack, AttackElement attackElement, FieldObjectId targetId);
        HitAttackResultModel CreateHitAttackResult(IAttackModel attack, AttackSubElement attackSubElement, FieldObjectId targetId);
        HitAttackResultModel CreatePickUpItemHitAttackResult(AttackElement attackElement, FieldObjectId placedItemId, FieldObjectId targetId);
        PlacedItemAttackResultModel CreatePlacedItem(
            IAttackModel attack, 
            BattleSide placedItemBattleSide,
            KomaId placedItemKomaId,
            FieldCoordV2 placedItemPos,
            AttackElement attackElement);
        DeckAttackResultModel CreateDeckAttackResult(
            IAttackModel attack,
            AttackElement attackElement,
            MasterDataId targetCharacterId,
            BattleSide targetBattleSide);
        DeckAttackResultModel CreateDeckAttackResult(
            IAttackModel attack,
            AttackSubElement attackSubElement,
            MasterDataId targetCharacterId,
            BattleSide targetBattleSide);
    }
}
