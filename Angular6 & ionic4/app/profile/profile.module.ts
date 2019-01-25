import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Routes, RouterModule } from '@angular/router';

import { IonicModule } from '@ionic/angular';
import { TranslateModule } from '@ngx-translate/core';

import { ProfilePage } from './profile.page';
import { HomePage } from '../home/home.page';
import { MyqrPage } from '../myqr/myqr.page';
import { NotificationsPage } from '../notifications/notifications.page';
import { SettingsPage } from '../settings/settings.page';
import { AboutPage } from '../about/about.page';
import { NewsPage } from '../news/news.page';
import { ContactPage } from '../contact/contact.page';
import { TermsPage } from '../terms/terms.page';

import { MapPage } from '../map/map.page';
import { CategoryPage } from '../category/category.page';
import { AccountPage } from '../account/account.page';
import { InvitePage } from '../invite/invite.page';
import { InnerCompanyPage } from '../inner-company/inner-company.page';
import { MaterialModule } from '../../app/services/modules/material-module';
import { HttpClient } from '@angular/common/http';

const routes: Routes = [
  {
    path: 'pages',
    component: ProfilePage,
    children: [
      {
        path: '',
        redirectTo: '/profile/pages/(home:home)',
        pathMatch: 'full',
      },
      {
        path: 'myqr',
        outlet: 'myqr',
        component: MyqrPage
      },
      {
        path: 'home',
        outlet: 'home',
        component: HomePage
      },
      {
        path: 'notifications',
        outlet: 'notifications',
        component: NotificationsPage
      },
      {
        path: 'account',
        outlet: 'account',
        component: AccountPage
      },
      {
        path: 'invite',
        outlet: 'invite',
        component: InvitePage
      },
      {
        path: 'settings',
        outlet: 'other',
        component: SettingsPage
      },
      {
        path: 'about',
        outlet: 'other',
        component: AboutPage
      },
      {
        path: 'news',
        outlet: 'other',
        component: NewsPage
      },
      {
        path: 'contact',
        outlet: 'other',
        component: ContactPage
      },
      {
        path: 'terms',
        outlet: 'other',
        component: TermsPage
      },
      {
        path: 'category/:id',
        outlet: 'other',
        component: CategoryPage
      },
      {
        path: "company/:id/:address_id",
        outlet: "other",
        component: MapPage
      },
      {
        path: "inner-company/:id",
        outlet: "other",
        component: InnerCompanyPage
      }
    ]
  },
  {
    path: '',
    redirectTo: '/profile/pages',
    pathMatch: 'full'
  }
];


@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    TranslateModule,
    MaterialModule,
    RouterModule.forChild(routes)
  ],
  exports: [
    TranslateModule
  ],
  providers: [TranslateModule],
  declarations: [
    ProfilePage,
    HomePage,
    MyqrPage,
    InvitePage,
    NotificationsPage,
    AccountPage,
    AboutPage,
    ContactPage,
    NewsPage,
    TermsPage,
    CategoryPage,
    MapPage,
    InnerCompanyPage,
    SettingsPage]
})
export class ProfilePageModule { }
