using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using Zenject;

namespace GLOW.Modules.TutorialTipDialog.Domain.UseCase
{
    public class TutorialTipDialogUseCase
    {
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }

        public TutorialTipDialogUseCaseModel GetTutorialTipDialogModel(MasterDataId tutorialTipId)
        {
            var models = MstTutorialRepository.GetMstTutorialTipModels(tutorialTipId);

            if (models.IsEmpty())
            {
                return TutorialTipDialogUseCaseModel.Empty;
            }
            
            var tipModels = new List<TutorialTipModel>();
            
            for (var i = 0; i < models.Count; i++)
            {
                var model = models[i];

                // 末尾の場合は次へにしない
                var flag = i == models.Count - 1
                    ? ShouldShowNextButtonTextFlag.False
                    : ShouldShowNextButtonTextFlag.True;

                var tutorialTipModel = new TutorialTipModel(
                    model.TutorialTipDialogTitle,
                    TutorialTipAssetPath.FromAssetKey(model.TutorialTipAssetKey),
                    flag
                );

                tipModels.Add(tutorialTipModel);
            }

            return new TutorialTipDialogUseCaseModel(tipModels);
        }
    }
}
