using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEnemyCharacterDataTranslator
    {
        public static MstEnemyCharacterModel ToEnemyCharacterModel(
            MstEnemyCharacterData data, 
            MstEnemyCharacterI18nData i18n)
        {
            return new MstEnemyCharacterModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstSeriesId),
                new UnitAssetKey(data.AssetKey),
                new CharacterName(i18n.Name),
                new UnitDescription(i18n.Description),
                new VisibleOnEncyclopediaFlag(data.IsDisplayedEncyclopedia),
                new PhantomizedFlag(data.IsPhantomized)
            );
        }
    }
}
