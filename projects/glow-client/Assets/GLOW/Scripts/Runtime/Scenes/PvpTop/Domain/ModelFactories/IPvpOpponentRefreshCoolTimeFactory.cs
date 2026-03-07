using System;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public interface IPvpOpponentRefreshCoolTimeFactory
    {
        PvpOpponentRefreshCoolTime CalculateRefreshCoolTime();
    }
}
