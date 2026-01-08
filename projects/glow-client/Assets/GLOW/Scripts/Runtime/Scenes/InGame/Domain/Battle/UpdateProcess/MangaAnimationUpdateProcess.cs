using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class MangaAnimationUpdateProcess : IMangaAnimationUpdateProcess
    {
        public MangaAnimationUpdateProcessResult UpdateMangaAnimations(
            IReadOnlyList<MangaAnimationModel> mangaAnimationModels,
            IReadOnlyList<CharacterUnitModel> units,
            TickCount tickCount)
        {
            var updatedMangaAnimationModels = new List<MangaAnimationModel>();
            var startingMangaAnimations = new List<MangaAnimationModel>();

            foreach (var mangaAnimationModel in mangaAnimationModels)
            {
                // 条件未達成
                if (!mangaAnimationModel.IsActivated)
                {
                    if (mangaAnimationModel.MeetsCondition(units))
                    {
                        updatedMangaAnimationModels.Add(mangaAnimationModel with
                        {
                            IsActivated = ActivatedMangaAnimationFlag.True
                        });

                        if (mangaAnimationModel.RemainingAnimationStartDelay.IsZero())
                        {
                            startingMangaAnimations.Add(mangaAnimationModel);
                        }
                        continue;;
                    }

                    updatedMangaAnimationModels.Add(mangaAnimationModel);
                    continue;
                }

                // 演出開始遅延中
                if (!mangaAnimationModel.RemainingAnimationStartDelay.IsZero())
                {
                    var remainingAnimationStartDelay = mangaAnimationModel.RemainingAnimationStartDelay - tickCount;

                    updatedMangaAnimationModels.Add(mangaAnimationModel with
                    {
                        RemainingAnimationStartDelay = remainingAnimationStartDelay
                    });

                    if (remainingAnimationStartDelay.IsZero())
                    {
                        startingMangaAnimations.Add(mangaAnimationModel);
                    }
                    continue;
                }

                // 演出済み
                updatedMangaAnimationModels.Add(mangaAnimationModel);
            }

            return new MangaAnimationUpdateProcessResult(updatedMangaAnimationModels, startingMangaAnimations);
        }
    }
}
