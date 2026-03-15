using System.Collections.Generic;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstArtworkEffectDescriptionDataTranslator
    {
        public static MstArtworkEffectDescriptionModel Translate(MstArtworkEffectI18nData data)
        {
            var descriptions = new List<ArtworkEffectDescriptionModel>
            {
                new ArtworkEffectDescriptionModel(
                    new ArtworkGradeLevel(1),
                    new ArtworkEffectDescription(data.GradeLevel1EffectText)),
                new ArtworkEffectDescriptionModel(
                    new ArtworkGradeLevel(2),
                    new ArtworkEffectDescription(data.GradeLevel2EffectText)),
                new ArtworkEffectDescriptionModel(
                    new ArtworkGradeLevel(3),
                    new ArtworkEffectDescription(data.GradeLevel3EffectText)),
                new ArtworkEffectDescriptionModel(
                    new ArtworkGradeLevel(4),
                    new ArtworkEffectDescription(data.GradeLevel4EffectText)),
                new ArtworkEffectDescriptionModel(
                    new ArtworkGradeLevel(5),
                    new ArtworkEffectDescription(data.GradeLevel5EffectText))
            };

            return new MstArtworkEffectDescriptionModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstArtworkId),
                descriptions);
        }
    }
}
