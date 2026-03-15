using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class DefenseTargetDataTranslator
    {
        public static MstDefenseTargetModel Translate(MstDefenseTargetData data)
        {
            return new MstDefenseTargetModel(
                new MasterDataId(data.Id),
                string.IsNullOrEmpty(data.AssetKey)
                    ? DefenseTargetAssetKey.Empty
                    : new DefenseTargetAssetKey(data.AssetKey),
                new FieldCoordV2(data.Position, 0),
                new HP(data.Hp));
        }
    }
}
