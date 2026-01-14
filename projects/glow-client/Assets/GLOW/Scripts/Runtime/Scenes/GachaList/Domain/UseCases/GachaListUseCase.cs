using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.GachaContent.Domain.UseCases;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.GachaList.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class GachaListUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }
        [Inject] IGachaListElementUseCaseModelFactory GachaListElementUseCaseModelFactory { get; }

        public GachaListUseCaseModel UpdateAndGetGachaListUseCaseModel(MasterDataId initialOprGachaId)
        {
            var now = TimeProvider.Now;

            // 端末に情報保存(副作用)
            PreferenceRepository.SetGachaListViewLastOpenedDateTimeOffset(now);

            var oprGachaModels = OprGachaRepository.GetOprGachaModelsByDataTime(now);
            // ガチャを優先度順にソート
            var sortedOprGachaModels = GachaEvaluator.SortOprGachaModelByPriority(oprGachaModels);

            var filterdTutorialOprGachaModels = sortedOprGachaModels
                .Where(m => m.GachaType != GachaType.Tutorial)
                .ToList();

            var initialShowOprGachaId = GetInitialShowOprGachaId(
                initialOprGachaId,
                filterdTutorialOprGachaModels.Select(m => m.Id).ToList()
            );

            var useCaseModel = new GachaListUseCaseModel(
                initialShowOprGachaId,
                CreateTutorialGachaListUseCaseElementModel(),
                CreateGachaListUseCaseElementModels(filterdTutorialOprGachaModels)
            );

            return useCaseModel;
        }

        MasterDataId GetInitialShowOprGachaId(
            MasterDataId initialOprGachaId,
            IReadOnlyList<MasterDataId> sortedOprGachaModelIds)
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            if (gameFetchOtherModel.TutorialStatus == TutorialSequenceIdDefinitions.TutorialMainPart_start)
            {
                // チュートリアル中はチュートリアルガチャを優先して表示
                var tutorialOprGachaModel = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now)
                    .First(m => m.GachaType == GachaType.Tutorial);
                return tutorialOprGachaModel.Id;
            }

            // 初期表示ガチャIDが存在し、かつ開催期間内なIDの場合はそれを返す
            if (!initialOprGachaId.IsEmpty() &&
                sortedOprGachaModelIds.Contains(initialOprGachaId))
            {
                return initialOprGachaId;
            }

            // 優先度に従ってものを返す
            return sortedOprGachaModelIds.Any() ? sortedOprGachaModelIds[0] : MasterDataId.Empty;
        }

        GachaListElementUseCaseModel CreateTutorialGachaListUseCaseElementModel()
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            if (gameFetchOtherModel.TutorialStatus != TutorialSequenceIdDefinitions.TutorialMainPart_start)
            {
                return GachaListElementUseCaseModel.Empty;
            }

            var tutorialOprGachaModel = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now)
                .First(m => m.GachaType == GachaType.Tutorial);

            return GachaListElementUseCaseModelFactory.Create(tutorialOprGachaModel.Id);
        }

        IReadOnlyList<GachaListElementUseCaseModel> CreateGachaListUseCaseElementModels(IReadOnlyList<OprGachaModel> activeOprGachaModels)
        {
            return activeOprGachaModels
                .Select(m => GachaListElementUseCaseModelFactory.Create(m.Id))
                .ToList();
        }

    }
}
