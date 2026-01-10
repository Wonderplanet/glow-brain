using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.Resolver
{
    public class MstCurrentPvpModelResolver : IMstCurrentPvpModelResolver
    {
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }
        
        MstPvpModel IMstCurrentPvpModelResolver.CreateMstPvpModel(ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpModel = MstPvpDataRepository.GetMstPvpModelFirstOrDefault(sysPvpSeasonId);
            if (!mstPvpModel.IsEmpty()) return mstPvpModel;
            
            mstPvpModel = MstPvpDataRepository.GetMstPvpModelFirstOrDefault(PvpConst.DefaultSysPvpSeasonId);
            return mstPvpModel;
        }

        MstPvpBattleModel IMstCurrentPvpModelResolver.CreateMstPvpBattleModel(ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpBattleModel = MstPvpDataRepository.GetMstPvpBattleModelFirstOrDefault(sysPvpSeasonId);
            if (!mstPvpBattleModel.IsEmpty()) return mstPvpBattleModel;
            
            mstPvpBattleModel = MstPvpDataRepository.GetMstPvpBattleModelFirstOrDefault(PvpConst.DefaultSysPvpSeasonId);
            return mstPvpBattleModel;
        }

        IReadOnlyList<MstPvpRewardGroupModel> IMstCurrentPvpModelResolver.CreateMstPvpRewardGroups(ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpRewardGroups = MstPvpDataRepository.GetMstPvpRewardGroups(sysPvpSeasonId);
            if (!mstPvpRewardGroups.IsEmpty()) return mstPvpRewardGroups;
            
            mstPvpRewardGroups = MstPvpDataRepository.GetMstPvpRewardGroups(PvpConst.DefaultSysPvpSeasonId);
            return mstPvpRewardGroups;
        }
    }
}