using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class TutorialService : ITutorialService
    {
        [Inject] TutorialApi TutorialApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<TutorialUpdateStatusResultModel> ITutorialService.UpdateTutorialStatus(
            CancellationToken cancellationToken,
            TutorialFunctionName tutorialFunctionName)
        {
            try
            {
                var data = await TutorialApi.UpdateStatus(cancellationToken, tutorialFunctionName.Value);
                return TutorialUpdateStatusResultDataTranslator.Translate(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<TutorialGachaDrawResultModel> ITutorialService.TutorialGachaDraw(CancellationToken cancellationToken)
        {
            try
            {
                var data = await TutorialApi.GachaDraw(cancellationToken);
                var resultModels = data.GachaResults.Select(TutorialGachaResultDataTranslator.Translate).ToList();
                return new TutorialGachaDrawResultModel(resultModels);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<TutorialGachaConfirmResultModel> ITutorialService.GachaConfirm(CancellationToken cancellationToken)
        {
            try
            {
                var data = await TutorialApi.GachaConfirm(cancellationToken);
                var model = TutorialGachaConfirmResultDataTranslator.Translate(data);
                return model;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<TutorialStageStartResultModel> ITutorialService.StartTutorialStage(
            CancellationToken cancellationToken,
            TutorialFunctionName tutorialFunctionName,
            PartyNo partyNo)
        {
            try
            {
               var data = await TutorialApi.StageStart(cancellationToken, tutorialFunctionName.Value, partyNo.Value);
                return TutorialStageStartResultDataTranslator.Translate(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<TutorialStageEndResultModel> ITutorialService.EndTutorialStage(
            CancellationToken cancellationToken,
            TutorialFunctionName tutorialFunctionName)
        {
            try
            {
                var data = await TutorialApi.StageEnd(cancellationToken, tutorialFunctionName.Value);
                return TutorialStageEndResultDataTranslator.Translate(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<TutorialUnitLevelUpResultModel> ITutorialService.TutorialUnitLevelUp(
            CancellationToken cancellationToken, 
            TutorialFunctionName tutorialFunctionName, 
            UserDataId usrUnitId,
            UnitLevel level)
        {
            try
            {
                var data = await TutorialApi.UnitLevelUp(
                    cancellationToken, 
                    tutorialFunctionName.Value,
                    usrUnitId.Value,
                    level.Value);
                return TutorialUnitLevelUpResultDataTranslator.Translate(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
