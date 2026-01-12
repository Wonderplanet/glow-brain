using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeUserParameterUseCaseModel(
        UserLevel Level,
        RelativeUserExp Exp,
        RelativeUserExp NextExp,
        Coin Coin,
        Stamina Stamina,
        Stamina MaxStamina,
        DateTimeOffset? StaminaUpdatedAt,
        FreeDiamond FreeDiamond,
        PaidDiamond PaidDiamond);

}
