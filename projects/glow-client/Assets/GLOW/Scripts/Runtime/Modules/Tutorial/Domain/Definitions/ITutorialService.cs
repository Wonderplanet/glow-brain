using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Definitions
{
    public interface ITutorialService
    {
        UniTask<TutorialUpdateStatusResultModel> UpdateTutorialStatus(CancellationToken cancellationToken, TutorialFunctionName tutorialFunctionName);
        UniTask<TutorialGachaDrawResultModel> TutorialGachaDraw(CancellationToken cancellationToken);
        UniTask<TutorialGachaConfirmResultModel> GachaConfirm(CancellationToken cancellationToken);
        UniTask<TutorialStageStartResultModel> StartTutorialStage(
            CancellationToken cancellationToken,
            TutorialFunctionName tutorialFunctionName,
            PartyNo partyNo);
        UniTask<TutorialStageEndResultModel> EndTutorialStage(
            CancellationToken cancellationToken,
            TutorialFunctionName tutorialFunctionName);

        UniTask<TutorialUnitLevelUpResultModel> TutorialUnitLevelUp(
            CancellationToken cancellationToken,
            TutorialFunctionName mstTutorialFunctionName,
            UserDataId usrUnitId, 
            UnitLevel level);
    }
}
