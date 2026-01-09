using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class AccountBanNoticeUseCase
    {
        [Inject] IUserService UserService { get; }

        public async UniTask<UserMyId> GetAccountBanUserMyId(CancellationToken cancellationToken)
        {
            var result = await UserService.Info(cancellationToken);
            return result.UserMyId;
        }
    }
}