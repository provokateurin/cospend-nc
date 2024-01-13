<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Julien Veyssier <julien-nc@posteo.net>
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Cospend;

use OCA\Cospend\AppInfo\Application;

/**
 * @psalm-type CospendAccessLevel = value-of<Application::ACCESS_LEVELS>
 * @psalm-type CospendShareType = value-of<Application::SHARE_TYPES>
 *
 * @psalm-type CospendMember = array{
 *     activated: bool,
 *     userid: ?string,
 *     name: string,
 *     id: int,
 *     weight: float,
 *     color: array{r: int, g: int, b: int},
 *     lastchanged: int,
 * }
 *
 * @psalm-type CospendBaseShare = array{
 *     id: int,
 *     accesslevel: CospendAccessLevel,
 * }
 *
 * @psalm-type CospendUserShare = CospendBaseShare&array{
 *      type: Application::SHARE_TYPE_USER,
 *      userid: string,
 *      name: string,
 *      manually_added: bool,
 *  }
 *
 * @psalm-type CospendGroupShare = CospendBaseShare&array{
 *      type: Application::SHARE_TYPE_GROUP,
 *      groupid: string,
 *      name: string,
 *  }
 *
 * @psalm-type CospendCircleShare = CospendBaseShare&array{
 *     type: Application::SHARE_TYPE_CIRCLE,
 *     circleid: string,
 *     name: string,
 * }
 *
 * @psalm-type CospendPublicShare = CospendBaseShare&array{
 *     type: Application::SHARE_TYPE_PUBLIC_LINK,
 *     token: string,
 *     label: ?string,
 *     password: ?string,
 * }
 *
 * @psalm-type CospendShare = array<CospendUserShare|CospendGroupShare|CospendCircleShare|CospendPublicShare>
 *
 * @psalm-type CospendCurrency = array{
 *     id: int,
 *     name: string,
 *     exchange_rate: float,
 *  }
 *
 * @psalm-type CospendCategoryOrPaymentMode = array{
 *     id: int,
 *     projectid: string,
 *     name: ?string,
 *     color: ?string,
 *     order: int,
 *  }
 *
 * @psalm-type CospendProjectInfo = array{
 *      active_members: CospendMember[],
 *      members: CospendMember[],
 *      balance: array<int, float>,
 *      nb_bills: int,
 *      total_spent: float,
 *      nb_trashbin_bills: int,
 *      shares: CospendShare[],
 *      currencies: CospendCurrency[],
 *      categories: CospendCategoryOrPaymentMode[],
 *      paymentmodes: CospendCategoryOrPaymentMode[],
 *  }
 *
 * @psalm-type CospendProjectInfoAndMyAccessLevel = CospendProjectInfo&array{
 *      myaccesslevel: int,
 *  }
 */
class ResponseDefinitions {
}