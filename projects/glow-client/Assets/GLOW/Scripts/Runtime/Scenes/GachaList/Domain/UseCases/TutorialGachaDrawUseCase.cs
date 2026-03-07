using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class TutorialGachaDrawUseCase
    {
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGachaCacheRepository GachaCacheRepository { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        // 演出・結果再生用データの確認
        public async UniTask GachaDraw(CancellationToken cancellationToken)
        {
            var result = await TutorialService.TutorialGachaDraw(cancellationToken);

            // 演出・結果用データ保存
            GachaCacheRepository.SaveGachaResultModels(result.GachaResultModels);

            // チュートリアルガシャのIDを取得
            var oprGachaModels = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now) ;
            var tutorialGachaModel = oprGachaModels.Find(model => model.GachaType == GachaType.Tutorial);

            // 引き直しのためにチュートリアル用モデルを保存
            var drawInfo = GachaDrawInfoModel.CreateTutorialDrawInfoModel(tutorialGachaModel.Id);
            GachaCacheRepository.SaveGachaDrawType(drawInfo);
        }
    }
}
