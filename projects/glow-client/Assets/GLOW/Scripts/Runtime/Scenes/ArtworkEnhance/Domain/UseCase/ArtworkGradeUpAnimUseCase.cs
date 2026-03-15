using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCase
{
    public class ArtworkGradeUpAnimUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository ArtworkDataRepository { get; }
        [Inject] IMstArtworkEffectDescriptionDataRepository ArtworkEffectDescriptionDataRepository { get; }
        [Inject] IMstConfigRepository ConfigRepository { get; }

        public ArtworkGradeUpAnimUseCaseModel CreateArtworkGradeUpAnim(MasterDataId mstArtworkId)
        {
            var userArtworkModel = GameRepository.GetGameFetchOther().UserArtworkModels
                .FirstOrDefault(artwork => artwork.MstArtworkId == mstArtworkId, UserArtworkModel.Empty);

            var artworkName = ArtworkDataRepository.GetArtwork(mstArtworkId).Name;

            // 前のグレードレベルを取得
            var beforeArtworkGradeLevel = userArtworkModel.Grade.GetPrevGradeLevel();

            // グレードが最大かどうかを判定
            var maxArtworkGradeLevel = ConfigRepository.GetConfig(MstConfigKey.ArtworkGradeCap).Value.ToInt();
            var isGradeMax = userArtworkModel.Grade.Value >= maxArtworkGradeLevel;

            // グレードアップ後の効果説明を取得
            var artworkEffectDescription =
                ArtworkEffectDescriptionDataRepository.GetArtworkEffectDescriptionFirstOrDefault(mstArtworkId).Descriptions
                .FirstOrDefault(description => description.GradeLevel == userArtworkModel.Grade,
                    ArtworkEffectDescriptionModel.Empty).Description;

            return new ArtworkGradeUpAnimUseCaseModel(
                artworkName,
                beforeArtworkGradeLevel,
                userArtworkModel.Grade,
                artworkEffectDescription,
                new ArtworkGradeMaxLimitFlag(isGradeMax)
                );
        }
    }
}
