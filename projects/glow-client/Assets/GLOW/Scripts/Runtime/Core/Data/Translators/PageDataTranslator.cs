using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public static class PageDataTranslator
    {
        public static MstPageModel ToPageModel(MstPageData mstPageData, IReadOnlyList<MstKomaLineData> lineDatas)
        {
            var lines = lineDatas
                .Where(l => l.MstPageId == mstPageData.Id)
                .Select((l, index) => GenerateMstKomaLineModel(l, index))
                .ToList();

            return new MstPageModel(new MasterDataId(mstPageData.Id), lines);
        }

        static MstKomaLineModel GenerateMstKomaLineModel(MstKomaLineData data, int index)
        {
            var mstKomaModels = GenerateKomaModels(data,index);
            return new MstKomaLineModel(
                data.Height,
                new KomaSetTypeAssetPath(KomaAssetPath.GetKomaLineAssetPath(data.KomaLineLayoutAssetKey)),
                mstKomaModels);
        }

        static IReadOnlyList<MstKomaModel> GenerateKomaModels(MstKomaLineData lineData, int index)
        {
            List<MstKomaModel> result = new List<MstKomaModel>();
            if (!string.IsNullOrEmpty(lineData.Koma1AssetKey))
            {
                var id = $"koma{index}-{0}";
                result.Add(GenerateMstKomaModel(
                    id,
                    lineData.Koma1AssetKey,
                    lineData.Koma1Width,
                    lineData.Koma1BackGroundOffset,
                    lineData.Koma1EffectType,
                    lineData.Koma1EffectTargetSide,
                    lineData.Koma1EffectTargetColors,
                    lineData.Koma1EffectTargetRoles,
                    lineData.Koma1EffectParameter1,
                    lineData.Koma1EffectParameter2));
            }
            if (!string.IsNullOrEmpty(lineData.Koma2AssetKey))
            {
                var id = $"koma{index}-{1}";
                result.Add(GenerateMstKomaModel(
                    id,
                    lineData.Koma2AssetKey,
                    lineData.Koma2Width,
                    lineData.Koma2BackGroundOffset,
                    lineData.Koma2EffectType,
                    lineData.Koma2EffectTargetSide,
                    lineData.Koma2EffectTargetColors,
                    lineData.Koma2EffectTargetRoles,
                    lineData.Koma2EffectParameter1,
                    lineData.Koma2EffectParameter2));
            }
            if (!string.IsNullOrEmpty(lineData.Koma3AssetKey))
            {
                var id = $"koma{index}-{2}";
                result.Add(GenerateMstKomaModel(
                    id,
                    lineData.Koma3AssetKey,
                    lineData.Koma3Width,
                    lineData.Koma3BackGroundOffset,
                    lineData.Koma3EffectType,
                    lineData.Koma3EffectTargetSide,
                    lineData.Koma3EffectTargetColors,
                    lineData.Koma3EffectTargetRoles,
                    lineData.Koma3EffectParameter1,
                    lineData.Koma3EffectParameter2));
            }
            if (!string.IsNullOrEmpty(lineData.Koma4AssetKey))
            {
                var id = $"koma{index}-{3}";
                result.Add(GenerateMstKomaModel(
                    id,
                    lineData.Koma4AssetKey,
                    lineData.Koma4Width,
                    lineData.Koma4BackGroundOffset,
                    lineData.Koma4EffectType,
                    lineData.Koma4EffectTargetSide,
                    lineData.Koma4EffectTargetColors,
                    lineData.Koma4EffectTargetRoles,
                    lineData.Koma4EffectParameter1,
                    lineData.Koma4EffectParameter2));
            }

            return result;
        }

        static MstKomaModel GenerateMstKomaModel(
            string id,
            string assetKey,
            float? width,
            float? backgroundOffset,
            KomaEffectType? komaEffectType,
            KomaEffectTargetSide? komaEffectTargetSide,
            string komaEffectTargetColors,
            string komaEffectTargetRoles,
            string komaEffectParameter1,
            string komaEffectParameter2)
        {
            var komaEffectParameter1Obj = komaEffectType!= null &&komaEffectType != KomaEffectType.None
                ? new KomaEffectParameter(komaEffectParameter1)
                : KomaEffectParameter.Empty;

            var komaEffectParameter2Obj = komaEffectType!= null && komaEffectType != KomaEffectType.None
                ? new KomaEffectParameter(komaEffectParameter2)
                : KomaEffectParameter.Empty;

            return new MstKomaModel(
                new KomaId(id),
                new KomaBackgroundAssetKey(assetKey),
                width == null ? 0f : width.Value,
                backgroundOffset == null ? KomaBackgroundOffset.Empty : new KomaBackgroundOffset(backgroundOffset.Value),
                komaEffectType == null ? KomaEffectType.None : komaEffectType.Value,
                komaEffectParameter1Obj,
                komaEffectParameter2Obj,
                komaEffectTargetSide == null ? KomaEffectTargetSide.All : komaEffectTargetSide.Value,
                EnumListTranslator.ToEnumList<CharacterColor>(komaEffectTargetColors),
                EnumListTranslator.ToEnumList<CharacterUnitRoleType>(komaEffectTargetRoles));
        }
    }
}
