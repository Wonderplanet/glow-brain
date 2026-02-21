using GLOW.Scenes.PvpInfo.Domain.Model;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.PvpInfo.Domain.UseCase
{
    public class PvpInfoUseCase
    {
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        
        public PvpInfoUseCaseModel GetModel(ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonId);
            return new PvpInfoUseCaseModel(mstPvpModel.Description);
        }
    }
}
